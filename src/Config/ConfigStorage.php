<?php

/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Baleen\Cli\Config;

use Baleen\Migrations\Exception\InvalidArgumentException;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ConfigStorage.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class ConfigStorage
{
    /** @var FilesystemInterface */
    protected $projectFileSystem;

    /** @var array */
    protected $localConfigStack;

    /** @var Processor */
    protected $processor;

    /** @var string */
    protected $configClass;

    /** @var ConfigurationInterface */
    protected $definition;

    /** @var string */
    protected $defaultFileName;

    /**
     * ConfigStorage constructor.
     *
     * @param string              $configClass       The FQN of the configuration file to be loaded.
     * @param FilesystemInterface $projectFileSystem
     * @param array               $localConfigStack  Array of files that contain configuration information in PHP
     *                                               arrays (see ./config folder in this project)
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        $configClass,
        FilesystemInterface $projectFileSystem,
        array $localConfigStack = []
    ) {
        foreach ($localConfigStack as $file) {
            if (!is_file($file) || !is_readable($file)) {
                throw new InvalidArgumentException(sprintf(
                    'Config file "%s" must be a readable file.',
                    $file
                ));
            }
        }
        $this->localConfigStack = $localConfigStack;

        $configClass = (string) $configClass;
        if (!class_exists($configClass)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid argument configClass: class "%s" does not exist.',
                $configClass
            ));
        }
        $this->configClass = $configClass;
        $configInstance = new $configClass();
        if (!$configInstance instanceof ConfigInterface) {
            throw new InvalidArgumentException(sprintf(
                'Class "%s" must be an instance of %s.',
                $configClass,
                ConfigInterface::class
            ));
        }
        $this->definition = $configInstance->getDefinition();
        $this->defaultFileName = $configInstance->getFileName();

        $this->processor = new Processor();
        $this->projectFileSystem = $projectFileSystem;
    }

    /**
     * @param string $configFileName The path to the consumer's config file (eg .baleen.yml) relative to the project
     *                               filesystem
     *
     * @return Config
     */
    public function load($configFileName = null)
    {
        $configs = [];

        // load all local configs (config files that are not user-facing)
        $localConfig = [];
        foreach ($this->localConfigStack as $file) {
            $config = include $file;
            $localConfig = array_merge_recursive($localConfig, $config);
        }
        if (!empty($localConfig)) {
            $configs[] = $localConfig;
        }

        // load the current project's config file (user-facing)
        if (null === $configFileName) {
            $configFileName = $this->defaultFileName;
        }
        if ($this->projectFileSystem->has($configFileName)) {
            $configs[] = Yaml::parse($this->projectFileSystem->read($configFileName));
        }

        // validate and merge all configs
        $config = $this->processor->processConfiguration(
            $this->definition,
            $configs
        );

        return new $this->configClass($config);
    }

    /**
     * @param ConfigInterface $config
     *
     * @return bool
     */
    public function write(ConfigInterface $config)
    {
        $fileName = $config->getFileName() ?: Config::CONFIG_FILE_NAME;
        $array = $config->getCleanArray();
        $contents = Yaml::dump($array);

        return $this->projectFileSystem->write($fileName, $contents);
    }

    /**
     * Returns whether the specified configuration has an existing user-facing config file.
     *
     * @param ConfigInterface $config
     *
     * @return bool
     */
    public function isInitialized(ConfigInterface $config)
    {
        return $this->projectFileSystem->has($config->getFileName());
    }
}
