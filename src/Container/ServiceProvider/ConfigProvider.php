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

namespace Baleen\Cli\Container\ServiceProvider;

use Baleen\Cli\Config\Config;
use Baleen\Cli\Config\ConfigStorage;
use Baleen\Cli\Container\Services;
use League\Container\ServiceProvider;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

/**
 * Class ConfigProvider.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class ConfigProvider extends ServiceProvider
{
    protected $provides = [
        Services::CONFIG,
        Services::CONFIG_STORAGE,
    ];

    /**
     * Use the register method to register items with the container via the
     * protected $this->container property or the `getContainer` method
     * from the ContainerAwareTrait.
     */
    public function register()
    {
        $baseDir = getcwd();
        $baleenBaseDir = $this->getContainer()->get(Services::BALEEN_BASE_DIR);
        $this->getContainer()->singleton(Services::CONFIG_STORAGE, function () use ($baseDir, $baleenBaseDir) {
            $configFiles = glob(implode(DIRECTORY_SEPARATOR, [$baleenBaseDir, 'config', '*.php']));
            $configFilesystem = new Filesystem(new Local($baseDir));

            return new ConfigStorage(Config::class, $configFilesystem, $configFiles);
        });
        $this->getContainer()->singleton(
            Services::CONFIG,
            function (ConfigStorage $configStorage) use ($baseDir) {
                return $configStorage->load(Config::CONFIG_FILE_NAME);
            }
        )->withArgument(Services::CONFIG_STORAGE);
    }
}
