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
     */
    public function __construct(array $config = [])
    {
        if (!empty($config)) {
            $processor = new Processor();
            $config = $processor->processConfiguration(
                $this->getDefinition(),
                [$config]
            );
            $this->config = $config;
        }
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
     * Returns a sorted list of plugins
     *
     * @return array
     */
    public function getPlugins()
    {
        $plugins = $this->config['plugins'];
        ksort($plugins, SORT_NUMERIC);
        return $plugins;
    }

    /**
     * @return string[]
     */
    public function getMigrationsConfig()
    {
        return $this->config['migrations'];
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
        unset($config['plugins']);

        return $config;
    }
}
