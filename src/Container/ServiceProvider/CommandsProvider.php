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

use Baleen\Cli\Command\AbstractCommand;
use Baleen\Cli\Command\InitCommand;
use Baleen\Cli\Command\Repository\AbstractRepositoryCommand;
use Baleen\Cli\Command\Repository\CreateCommand;
use Baleen\Cli\Command\Repository\LatestCommand as RepositoryLatest;
use Baleen\Cli\Command\Repository\ListCommand as RepositoryList;
use Baleen\Cli\Command\Storage\AbstractStorageCommand;
use Baleen\Cli\Command\Storage\LatestCommand as StorageLatest;
use Baleen\Cli\Command\Timeline\AbstractTimelineCommand;
use Baleen\Cli\Command\Timeline\ExecuteCommand;
use Baleen\Cli\Command\Timeline\MigrateCommand;
use Baleen\Migrations\Version\Comparator\DefaultComparator;
use League\Container\ServiceProvider;

/**
 * Class CommandsProvider
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class CommandsProvider extends ServiceProvider
{
    const SERVICE_COMMANDS = 'commands';

    protected $provides = [
        self::SERVICE_COMMANDS,
        StorageLatest::class,
        InitCommand::class,
        RepositoryLatest::class,
        RepositoryList::class,
        CreateCommand::class,
        ExecuteCommand::class,
        MigrateCommand::class,
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

        // storage
        $container->add(StorageLatest::class);
        // repository
        $container->add(RepositoryLatest::class);
        $container->add(RepositoryList::class);
        $container->add(CreateCommand::class);
        // timeline
        $container->add(ExecuteCommand::class);
        $container->add(MigrateCommand::class);
        // other
        $container->add(InitCommand::class);

        $provides = $this->provides;
        $container->add(self::SERVICE_COMMANDS, function () use ($container, $provides) {
            $commands = [];
            foreach ($provides as $command) {
                if ($command !== self::SERVICE_COMMANDS) {
                    $commands[] = $container->get($command);
                }
            }
            return $commands;
        });

        // register inflectors for the different types of commands
        $container->inflector(AbstractRepositoryCommand::class)
            ->invokeMethod('setRepository', [RepositoryProvider::SERVICE_REPOSITORY])
            ->invokeMethod('setFilesystem', [RepositoryProvider::SERVICE_FILESYSTEM]);

        $container->inflector(AbstractCommand::class)
            ->invokeMethod('setComparator', [DefaultComparator::class])
            ->invokeMethod('setConfig', [AppConfigProvider::SERVICE_CONFIG]);

        $container->inflector(AbstractStorageCommand::class)
            ->invokeMethod('setStorage', [StorageProvider::SERVICE_STORAGE]);

        $container->inflector(AbstractTimelineCommand::class)
            ->invokeMethod('setTimeline', [TimelineProvider::SERVICE_TIMELINE]);

        $container->inflector(InitCommand::class)
            ->invokeMethod('setConfigStorage', [AppConfigProvider::SERVICE_CONFIG_STORAGE]);
    }
}
