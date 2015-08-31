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

use Baleen\Cli\BaseCommand;
use Baleen\Cli\Command\Config\InitCommand;
use Baleen\Cli\Command\Config\InitHandler;
use Baleen\Cli\Command\Repository\CreateCommand;
use Baleen\Cli\Command\Repository\CreateHandler;
use Baleen\Cli\Command\Repository\LatestCommand as RepositoryLatestCommand;
use Baleen\Cli\Command\Repository\LatestHandler as RepositoryLatestHandler;
use Baleen\Cli\Command\Repository\ListCommand;
use Baleen\Cli\Command\Repository\ListHandler;
use Baleen\Cli\Command\Storage\LatestCommand as StorageLatestCommand;
use Baleen\Cli\Command\Storage\LatestHandler as StorageLatestHandler;
use Baleen\Cli\Command\Timeline\ExecuteCommand;
use Baleen\Cli\Command\Timeline\ExecuteHandler;
use Baleen\Cli\Command\Timeline\MigrateCommand;
use Baleen\Cli\Command\Timeline\MigrateHandler;
use Baleen\Cli\Container\Services;
use League\Container\ContainerInterface;
use League\Container\ServiceProvider;
use League\Tactician\Setup\QuickStart;

/**
 * Class CommandsProvider.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class CommandsProvider extends ServiceProvider
{
    protected $provides = [
        Services::COMMANDS,
        Services::COMMAND_BUS,
    ];

    /** @var array */
    protected $commands = [
        Services::CMD_CONFIG_INIT => [
            'class' => InitCommand::class,
            'handler' => InitHandler::class,
        ],
        Services::CMD_REPOSITORY_CREATE => [
            'class' => CreateCommand::class,
            'handler' => CreateHandler::class,
        ],
        Services::CMD_REPOSITORY_LATEST => [
            'class' => RepositoryLatestCommand::class,
            'handler' => RepositoryLatestHandler::class,
        ],
        Services::CMD_REPOSITORY_LIST => [
            'class' => ListCommand::class,
            'handler' => ListHandler::class,
        ],
        Services::CMD_STORAGE_LATEST => [
            'class' => StorageLatestCommand::class,
            'handler' => StorageLatestHandler::class,
        ],
        Services::CMD_TIMELINE_EXECUTE => [
            'class' => ExecuteCommand::class,
            'handler' => ExecuteHandler::class,
        ],
        Services::CMD_TIMELINE_MIGRATE => [
            'class' => MigrateCommand::class,
            'handler' => MigrateHandler::class,
        ],
    ];

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        $this->provides = array_merge($this->provides, array_keys($this->commands));
    }

    /**
     * Use the register method to register items with the container via the
     * protected $this->container property or the `getContainer` method
     * from the ContainerAwareTrait.
     */
    public function register()
    {
        $container = $this->getContainer();

        $commands = $this->commands;

        // add all message classes to the container
        foreach ($commands as $alias => $config) {
            $container->add($alias, $config['class']);
        }

        // setup the command bus to know which handler to use for each message class
        $container->singleton(Services::COMMAND_BUS, function () use ($commands) {
            $map = [];
            foreach ($commands as $alias => $config) {
                $message = $config['class'];
                $handler = $config['handler'];
                $map[$message] = new $handler();
            }

            return QuickStart::create($map);
        });

        // create a service (that's just an array) that has a list of all the commands for the app
        $container->add(Services::COMMANDS, function (ContainerInterface $container) use ($commands) {
            $commandList = [];
            foreach ($commands as $alias => $config) {
                $commandList[] = new BaseCommand($container, $alias, $config['class']);
            }

            return $commandList;
        })->withArgument('League\Container\ContainerInterface');
    }
}
