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

namespace Baleen\Cli\Provider;

use Baleen\Cli\Config\Config;
use Baleen\Cli\Repository\MigrationMapperService;
use Baleen\Cli\Repository\MigrationMapperServiceFactory;
use Baleen\Cli\Repository\MigrationRepositoryServiceFactory;
use Baleen\Migrations\Migration\Factory\FactoryInterface;
use Baleen\Migrations\Migration\Factory\SimpleFactory;
use Baleen\Migrations\Service\Runner\MigrationRunner;
use Baleen\Migrations\Service\Runner\MigrationRunnerInterface;
use Baleen\Migrations\Version\Comparator\ComparatorInterface;
use Composer\Autoload\ClassLoader;
use League\Container\ServiceProvider;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;

/**
 * Class MigrationRepositoryProvider.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class MigrationRepositoryProvider extends AbstractServiceProvider
{
    protected $provides = [
        Services::MIGRATION_REPOSITORY,
        Services::MIGRATION_REPOSITORY_FILESYSTEM,
        Services::MIGRATION_FACTORY,
        MigrationMapperService::class,
    ];

    /**
     * @inheritdoc
     */
    public function register()
    {
        $container = $this->getContainer();

        // Migration Factory service
        $container->share(Services::MIGRATION_FACTORY, SimpleFactory::class);

        // Filesystem service
        $container->share(Services::MIGRATION_REPOSITORY_FILESYSTEM, function (Config $appConfig) {
            $adapter = new Local(dirname($appConfig->getConfigFilePath()));

            return new Filesystem($adapter);
        })->withArgument(Services::CONFIG);

        // Migration Mapper service
        $container->share(
            MigrationMapperService::class,
            function (MigrationMapperServiceFactory $factory) {
                return $factory->create();
            }
        )->withArguments([MigrationMapperServiceFactory::class]);

        // Migration Repository service
        $container->share(
            Services::MIGRATION_REPOSITORY,
            function (MigrationRepositoryServiceFactory $factory) {
                // a factory class is cleaner easier to test
                return $factory->create();
            }
        )->withArguments([MigrationRepositoryServiceFactory::class]);
    }
}
