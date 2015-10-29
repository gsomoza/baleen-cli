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
use Baleen\Cli\Exception\CliException;
use Baleen\Migrations\Migration\Factory\FactoryInterface;
use Baleen\Migrations\Migration\Factory\SimpleFactory;
use Baleen\Migrations\Repository\DirectoryRepository;
use Baleen\Migrations\Repository\RepositoryStack;
use League\Container\ServiceProvider;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;

/**
 * Class RepositoryProvider.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class RepositoryProvider extends ServiceProvider
{
    protected $provides = [
        Services::REPOSITORY,
        Services::REPOSITORY_FILESYSTEM,
        Services::MIGRATION_FACTORY,
    ];

    /**
     * @inheritdoc
     */
    public function register()
    {
        $container = $this->getContainer();

        $container->singleton(Services::MIGRATION_FACTORY, SimpleFactory::class);

        $container->singleton(Services::REPOSITORY_FILESYSTEM, function (Config $appConfig) {
            $adapter = new Local(dirname($appConfig->getConfigFilePath()));

            return new Filesystem($adapter);
        })->withArgument(Services::CONFIG);

        $container->singleton(
            Services::REPOSITORY,
            function (Config $config, FactoryInterface $migrationFactory, FilesystemInterface $filesystem) {
                $repositoryStack = new RepositoryStack();
                $directories = $config->getMigrationsDirectories();
                // to make sure classes in the migration directory are autoloaded
                /** @var \Composer\Autoload\ClassLoader $autoloader */
                $autoloader = $this->getContainer()->get(Services::AUTOLOADER);

                foreach ($directories as $ns => $directory) {
                    if (!$filesystem->has($directory)) {
                        $result = $filesystem->createDir($directory);
                        if (!$result) {
                            throw new CliException(sprintf(
                                'Could not create directory "$s.',
                                $directories
                            ));
                        }
                    } else {
                        $meta = $filesystem->getMetadata($directory);
                        if ($meta['type'] !== 'dir') {
                            throw new CliException(sprintf(
                                'Expected path "%s" to be a directory, but its a %s.',
                                $directory,
                                $meta['type']
                            ));
                        }
                    }

                    // normalize the namespace
                    $ns = rtrim($ns, '\\') . '\\';
                    // add to the autoloader if necessary
                    if (!in_array($ns, $autoloader->getPrefixes())
                        && !in_array($ns, $autoloader->getPrefixesPsr4())
                    ) {
                        $autoloader->addPsr4($ns, getcwd() . DIRECTORY_SEPARATOR . $directory);
                    }

                    $repo = new DirectoryRepository($directory, $migrationFactory);
                    $repositoryStack->addRepository($repo);
                }

                return $repositoryStack;
            }
        )->withArguments([Services::CONFIG, Services::MIGRATION_FACTORY, Services::REPOSITORY_FILESYSTEM]);
    }
}
