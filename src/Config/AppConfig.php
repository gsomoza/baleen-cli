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
 * <https://github.com/baleen/migrations>.
 */

namespace Baleen\Baleen\Config;

use Baleen\Baleen\Exception\CliException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;

/**
 * Class AppConfig
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class AppConfig
{
    const CONFIG_FILE_NAME = '.baleen.yml';
    const VERSIONS_FILE_NAME = '.baleen_versions';

    /**
     * @return array
     */
    public function getDefaults()
    {
        return [
            'migrations'   => [
                'directory' => 'migrations',
                'namespace' => 'Migrations',
            ],
            'storage_file' => self::VERSIONS_FILE_NAME,
        ];
    }

    /**
     * @inheritDoc
     */
    public function __construct(array $config = [])
    {
        $mergedConfig = array_merge($this->getDefaults(), $config);
        $this->config = $mergedConfig;
    }

    /**
     * @return mixed
     */
    public function getMigrationsDirectoryPath()
    {
        return getcwd() . DIRECTORY_SEPARATOR . $this->config['migrations']['directory'];
    }

    public function getMigrationsNamespace()
    {
        return $this->config['migrations']['namespace'];
    }

    /**
     * @return mixed
     */
    public function getStorageFilePath()
    {
        return getcwd() . DIRECTORY_SEPARATOR . $this->getStorageFile();
    }

    public function getStorageFile()
    {
        return $this->config['storage_file'];
    }

    /**
     * @return mixed
     */
    public function getConfigFilePath()
    {
        return getcwd() . DIRECTORY_SEPARATOR . $this->getConfigFile();
    }

    public function getConfigFile()
    {
        return self::CONFIG_FILE_NAME;
    }

    public function write(array $data = null)
    {
        if (null === $data) {
            $data = $this->config;
        }
        $configFile = $this->getConfigFilePath();
        return file_put_contents($configFile, Yaml::dump(['baleen' => $data]));
    }

    /**
     * @param $configFile
     * @return static
     * @throws CliException
     */
    public static function loadFromFile($configFile)
    {
        if (!is_file($configFile) || !is_readable($configFile)) {
            throw new CliException(sprintf(
                'Configuration file "%s" could not be read.', $configFile
            ));
        }
        $rawConfig = Yaml::parse(file_get_contents($configFile));

        $processor = new Processor();
        $definition = new ConfigurationDefinition();
        $config = $processor->processConfiguration(
            $definition,
            $rawConfig
        );
        return new static($config);
    }
}
