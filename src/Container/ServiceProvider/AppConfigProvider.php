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

use Baleen\Cli\Config\AppConfig;
use Baleen\Cli\Config\ConfigFileStorage;
use League\Container\ServiceProvider;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

/**
 * Class AppConfigProvider
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class AppConfigProvider extends ServiceProvider
{
    const SERVICE_CONFIG = 'config';
    const SERVICE_CONFIG_STORAGE = 'config-writer';

    protected $provides = [
        self::SERVICE_CONFIG
    ];

    /**
     * Use the register method to register items with the container via the
     * protected $this->container property or the `getContainer` method
     * from the ContainerAwareTrait.
     *
     * @return void
     */
    public function register()
    {
        $baseDir = getcwd();
        $this->getContainer()->singleton(self::SERVICE_CONFIG_STORAGE, function() use ($baseDir) {
            $configFilesystem = new Filesystem(new Local($baseDir));
            return new ConfigFileStorage($configFilesystem);
        });
        $this->getContainer()->singleton(
            self::SERVICE_CONFIG,
            function (ConfigFileStorage $configStorage) use ($baseDir) {
                return $configStorage->isInitialized(AppConfig::CONFIG_FILE_NAME) ?
                       // its important to call "load" and not just "read"
                       $configStorage->load(AppConfig::CONFIG_FILE_NAME) :
                       new AppConfig();
            }
        )->withArgument(self::SERVICE_CONFIG_STORAGE);
    }
}
