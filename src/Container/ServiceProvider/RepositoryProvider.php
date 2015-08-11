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

use Baleen\Cli\Command\Repository\AbstractRepositoryCommand;
use Baleen\Cli\Config\AppConfig;
use Baleen\Cli\Exception\CliException;
use Baleen\Migrations\Repository\DirectoryRepository;
use League\Container\ServiceProvider;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

/**
 * Class RepositoryProvider
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class RepositoryProvider extends ServiceProvider
{
    const SERVICE_REPOSITORY = 'repository';
    const SERVICE_FILESYSTEM = 'repository-filesystem';

    protected $provides = [
        self::SERVICE_REPOSITORY,
        self::SERVICE_REPOSITORY,
    ];

    /**
     * @inheritdoc
     */
    public function register()
    {
        $container = $this->getContainer();

        $container->singleton(self::SERVICE_FILESYSTEM, function (AppConfig $appConfig) {
            $adapter = new Local(dirname($appConfig->getConfigFilePath()));
            return new Filesystem($adapter);
        })->withArgument(AppConfigProvider::SERVICE_CONFIG);

        $container->singleton(self::SERVICE_REPOSITORY, function (AppConfig $config) {
            $migrationsDir = $config->getMigrationsDirectoryPath();
            if (!is_dir($migrationsDir)) {
                $result = mkdir($migrationsDir, 0777, true);
                if (!$result) {
                    throw new CliException(sprintf(
                        'Could not create directory "$s.',
                        $migrationsDir
                    ));
                }
            }
            // make sure classes in the migration directory are autoloaded
            /** @var \Composer\Autoload\ClassLoader $autoloader */
            $autoloader = $this->getContainer()->get(DefaultProvider::SERVICE_AUTOLOADER);
            $autoloader->addPsr4($config->getMigrationsNamespace() . '\\', $migrationsDir);
            return new DirectoryRepository($migrationsDir);
        })->withArgument(AppConfigProvider::SERVICE_CONFIG);
    }
}
