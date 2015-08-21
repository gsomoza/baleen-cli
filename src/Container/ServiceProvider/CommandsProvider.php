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

use Baleen\Cli\Command\InitCommand;
use Baleen\Cli\Command\Repository\CreateCommand;
use Baleen\Cli\Command\Repository\LatestCommand as RepositoryLatest;
use Baleen\Cli\Command\Repository\ListCommand as RepositoryList;
use Baleen\Cli\Command\Storage\LatestCommand as StorageLatest;
use Baleen\Cli\Command\Timeline\ExecuteCommand;
use Baleen\Cli\Command\Timeline\MigrateCommand;
use Baleen\Cli\Container\Services;
use League\Container\ServiceProvider;

/**
 * Class CommandsProvider.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class CommandsProvider extends ServiceProvider
{
    protected $provides = [
        Services::COMMANDS,
        Services::CMD_CONFIG_INIT,
        Services::CMD_TIMELINE_EXECUTE,
        Services::CMD_TIMELINE_MIGRATE,
        Services::CMD_REPOSITORY_CREATE,
        Services::CMD_REPOSITORY_LATEST,
        Services::CMD_REPOSITORY_LIST,
        Services::CMD_STORAGE_LATEST,
    ];

    /**
     * Use the register method to register items with the container via the
     * protected $this->container property or the `getContainer` method
     * from the ContainerAwareTrait.
     */
    public function register()
    {
        $container = $this->getContainer();

        // storage
        $container->add(Services::CMD_STORAGE_LATEST, StorageLatest::class);
        // repository
        $container->add(Services::CMD_REPOSITORY_LATEST, RepositoryLatest::class);
        $container->add(Services::CMD_REPOSITORY_LIST, RepositoryList::class);
        $container->add(Services::CMD_REPOSITORY_CREATE, CreateCommand::class);
        // timeline
        $container->add(Services::CMD_TIMELINE_EXECUTE, ExecuteCommand::class);
        $container->add(Services::CMD_TIMELINE_MIGRATE, MigrateCommand::class);
        // other
        $container->add(Services::CMD_CONFIG_INIT, InitCommand::class);

        $provides = $this->provides;
        $container->add(Services::COMMANDS, function () use ($container, $provides) {
            $commands = [];
            foreach ($provides as $command) {
                if ($command !== Services::COMMANDS) {
                    $commands[] = $container->get($command);
                }
            }

            return $commands;
        });
    }
}
