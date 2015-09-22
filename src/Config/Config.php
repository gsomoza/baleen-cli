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

namespace Baleen\Cli\Config;

use Baleen\Cli\Container\ServiceProvider\CommandsProvider;
use Baleen\Cli\Container\ServiceProvider\ApplicationProvider;
use Baleen\Cli\Container\ServiceProvider\HelperSetProvider;
use Baleen\Cli\Container\ServiceProvider\RepositoryProvider;
use Baleen\Cli\Container\ServiceProvider\StorageProvider;
use Baleen\Cli\Container\ServiceProvider\TimelineProvider;
use Symfony\Component\Config\Definition\Processor;

/**
 * Class Config.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class Config implements ConfigInterface
{
    const CONFIG_FILE_NAME = '.baleen.yml';
    const VERSIONS_FILE_NAME = '.baleen_versions';

    /** @var array */
    private $config;

    /**
     * @param array $config
     * @param bool  $defaults
     */
    public function __construct(array $config = [], $defaults = true)
    {
        $configs = [$config];
        if ($defaults) {
            // insert at the beginning of the array
            array_unshift($configs, $this->getDefaults());
        }
        $processor = new Processor();
        $config = $processor->processConfiguration(
            $this->getDefinition(),
            $configs
        );
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getDefaults()
    {
        return [
            'providers' => $this->getProviderDefaults(),
            'migrations' => $this->getMigrationDefaults(),
            'storage' => $this->getStorageDefaults(),
        ];
    }

    /**
     * Default values for the migrations section.
     *
     * @return array
     */
    protected function getMigrationDefaults()
    {
        return [
            'directory' => 'migrations',
            'namespace' => 'Migrations',
        ];
    }

    /**
     * Default values for the storage section.
     *
     * @return array
     */
    protected function getStorageDefaults()
    {
        return [
            'file' => self::VERSIONS_FILE_NAME,
        ];
    }

    /**
     * Default values for the providers section.
     *
     * @return array
     */
    protected function getProviderDefaults()
    {
        return [
            'application' => ApplicationProvider::class,
            'storage' => StorageProvider::class,
            'repository' => RepositoryProvider::class,
            'timeline' => TimelineProvider::class,
            'helperSet' => HelperSetProvider::class,
            'commands' => CommandsProvider::class,
        ];
    }

    /**
     * getProviders.
     *
     * @return array
     */
    public function getProviders()
    {
        return $this->config['providers'];
    }

    /**
     * @return string
     */
    public function getMigrationsDirectoryPath()
    {
        return getcwd().DIRECTORY_SEPARATOR.$this->getMigrationsDirectory();
    }

    /**
     * @return mixed
     */
    public function getMigrationsDirectory()
    {
        return $this->config['migrations']['directory'];
    }

    /**
     * @return string
     */
    public function getMigrationsNamespace()
    {
        return $this->config['migrations']['namespace'];
    }

    /**
     * @return string
     */
    public function getStorageFilePath()
    {
        return getcwd().DIRECTORY_SEPARATOR.$this->getStorageFile();
    }

    /**
     * @return mixed
     */
    public function getStorageFile()
    {
        return $this->config['storage']['file'];
    }

    /**
     * @return string
     */
    public function getConfigFilePath()
    {
        return getcwd().DIRECTORY_SEPARATOR.$this->getFileName();
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return self::CONFIG_FILE_NAME;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->config;
    }

    /**
     * getDefinition.
     *
     * @return Definition
     */
    public function getDefinition()
    {
        return new Definition();
    }

    /**
     * Returns a clone of itself but only with settings that can be configured by the end-user.
     *
     * @return array
     */
    public function getCleanArray()
    {
        $config = $this->config;
        unset($config['providers']);

        return $config;
    }
}
