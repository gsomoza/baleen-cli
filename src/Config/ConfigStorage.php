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

use Baleen\Cli\Exception\CliException;
use League\Flysystem\FilesystemInterface;
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

    /** @var AppConfig */
    protected $config;

    /** @var Processor */
    protected $processor;

    /** @var ConfigurationDefinition */
    protected $definition;

    /**
     * ConfigStorage constructor.
     *
     * @param FilesystemInterface $projectFileSystem
     * @param array $defaultConfig
     */
    public function __construct(FilesystemInterface $projectFileSystem, array $defaultConfig = [])
    {
        $this->projectFileSystem = $projectFileSystem;
        $this->defaultConfig = $defaultConfig;

        $this->processor = new Processor();
        $this->definition = new ConfigurationDefinition();
    }

    /**
     * @param $externalFile
     *
     * @return AppConfig
     *
     * @throws CliException
     */
    public function read($externalFile = null)
    {
        if (null === $externalFile) {
            $externalFile = AppConfig::CONFIG_FILE_NAME;
        }
        $configs = [];
        if (!empty($this->defaultConfig)) {
            $configs[] = $this->defaultConfig;
        }
        if ($this->projectFileSystem->has($externalFile)) {
            $configs[] = Yaml::parse($this->projectFileSystem->read($externalFile));
        }

        $config = $this->processor->processConfiguration(
            $this->definition,
            $configs
        );

        return new AppConfig($config);
    }

    /**
     * Reads the file and loads the config into this instance.
     *
     * @param null $file
     *
     * @return AppConfig
     *
     * @throws CliException
     */
    public function load($file = null)
    {
        $config = $this->read($file);
        $this->setAppConfig($config);

        return $config;
    }

    /**
     * @param null $file
     * @param bool $defaultsOnly
     *
     * @return bool
     * @throws CliException
     */
    public function write($file = null, $defaultsOnly = true)
    {
        if (null === $file) {
            $file = $this->config->getConfigFileName();
        }
        $config = $this->getAppConfig()->toArray();
        if ($defaultsOnly) {
            unset($config['providers']); // we don't want to write that
        }
        $contents = Yaml::dump($config);

        return $this->projectFileSystem->write($file, $contents);
    }

    /**
     * @param $pathOrConfig
     *
     * @return bool
     */
    public function isInitialized($pathOrConfig = null)
    {
        if (null === $pathOrConfig) {
            $pathOrConfig = $this->config;
        }
        if (is_object($pathOrConfig) && $pathOrConfig instanceof AppConfig) {
            $path = $pathOrConfig->getConfigFileName();
        } else {
            $path = $pathOrConfig;
        }

        return null === $path ? false : $this->projectFileSystem->has($path);
    }

    /**
     * @return AppConfig
     */
    public function getAppConfig()
    {
        if (null === $this->config) {
            $this->config = new AppConfig();
        }

        return $this->config;
    }

    /**
     * @param AppConfig $config
     */
    public function setAppConfig($config)
    {
        $this->config = $config;
    }

    public function isLoaded()
    {
        return null !== $this->config;
    }

    /**
     * @return mixed
     */
    public function getConfigFileName()
    {
        return $this->getAppConfig()->getConfigFileName();
    }
}
