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

/**
 * Class AppConfig.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class AppConfig
{
    const CONFIG_FILE_NAME = '.baleen.yml';
    const VERSIONS_FILE_NAME = '.baleen_versions';

    /** @var array */
    private $config;

    /**
     * @inheritDoc
     */
    public function __construct(array $config = [])
    {
        $mergedConfig = array_merge($this->getDefaults(), $config);
        $this->config = $mergedConfig;
    }

    /**
     * @return array
     */
    public function getDefaults()
    {
        return [
            'migrations' => [
                'directory' => 'migrations',
                'namespace' => 'Migrations',
            ],
            'storage_file' => self::VERSIONS_FILE_NAME,
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
        return $this->config['storage_file'];
    }

    /**
     * @return string
     */
    public function getConfigFilePath()
    {
        return getcwd().DIRECTORY_SEPARATOR.$this->getConfigFileName();
    }

    /**
     * @return string
     */
    public function getConfigFileName()
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
}
