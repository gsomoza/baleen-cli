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

namespace Baleen\Baleen\Container;

use Baleen\Baleen\Application;
use Baleen\Baleen\ApplicationFactory;
use Baleen\Baleen\Config\AppConfig;
use Baleen\Baleen\Exception\CliException;
use Baleen\Baleen\Helper\ConfigHelper;
use Baleen\Migrations\Repository\DirectoryRepository;
use Baleen\Migrations\Storage\FileStorage;
use Baleen\Migrations\Version\Comparator\DefaultComparator;
use League\Container\ServiceProvider;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;

/**
 * Class DefaultServiceProvider
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class DefaultServiceProvider extends ServiceProvider
{

    const SERVICE_CONFIG     = 'config';
    const SERVICE_HELPERSET  = 'helperSet';
    const SERVICE_STORAGE    = 'storage';
    const SERVICE_REPOSITORY = 'repository';

    protected $provides = [
        self::SERVICE_CONFIG,
        self::SERVICE_HELPERSET,
        self::SERVICE_STORAGE,
        self::SERVICE_REPOSITORY,
        Application::class,
        QuestionHelper::class,
        ConfigHelper::class,
        DefaultComparator::class,
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
        $container = $this->getContainer();

        $container->add(Application::class, null, true)
            ->withArguments([
                CommandsServiceProvider::SERVICE_COMMANDS,
                self::SERVICE_HELPERSET
            ]);

        $container->add(self::SERVICE_STORAGE, function (AppConfig $config) {
                $storageFile = $config->getStorageFilePath();
                if (!file_exists($storageFile)) {
                    $result = touch($storageFile);
                    if (!$result) {
                        throw new CliException(sprintf(
                            'Could not write storage file "./%s"',
                            $config->getStorageFile()
                        ));
                    }
                }
                return new FileStorage($storageFile);
            })
            ->withArgument(self::SERVICE_CONFIG);

        $container->add(self::SERVICE_REPOSITORY, function (AppConfig $config){
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
                return new DirectoryRepository($migrationsDir);
            })
            ->withArgument(self::SERVICE_CONFIG);

        $container->add(QuestionHelper::class);
        $container->add(ConfigHelper::class)
            ->withArgument(self::SERVICE_CONFIG);

        $container->add(DefaultComparator::class);

        $container->add(self::SERVICE_HELPERSET, function() use ($container) {
            $helperSet = new HelperSet();
            $helperSet->set($container->get(QuestionHelper::class), 'question');
            $helperSet->set($container->get(ConfigHelper::class));
            return $helperSet;
        });
    }
}
