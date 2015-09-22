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
use Baleen\Cli\CommandBus\Config\InitMessage;
use Baleen\Cli\CommandBus\Config\InitHandler;
use Baleen\Cli\CommandBus\Config\StatusHandler;
use Baleen\Cli\CommandBus\Config\StatusMessage;
use Baleen\Cli\CommandBus\Factory\DefaultFactory;
use Baleen\Cli\CommandBus\Factory\MessageFactoryInterface;
use Baleen\Cli\CommandBus\Repository\CreateMessage;
use Baleen\Cli\CommandBus\Repository\CreateHandler;
use Baleen\Cli\CommandBus\Repository\LatestMessage as RepositoryLatestCommand;
use Baleen\Cli\CommandBus\Repository\LatestHandler as RepositoryLatestHandler;
use Baleen\Cli\CommandBus\Repository\ListMessage;
use Baleen\Cli\CommandBus\Repository\ListHandler;
use Baleen\Cli\CommandBus\Storage\LatestMessage as StorageLatestCommand;
use Baleen\Cli\CommandBus\Storage\LatestHandler as StorageLatestHandler;
use Baleen\Cli\CommandBus\Timeline\ExecuteMessage;
use Baleen\Cli\CommandBus\Timeline\ExecuteHandler;
use Baleen\Cli\CommandBus\Timeline\MigrateMessage;
use Baleen\Cli\CommandBus\Timeline\MigrateHandler;
use Baleen\Cli\Container\Services;
use Baleen\Cli\Exception\CliException;
use League\Container\Container;
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
            'message' => InitMessage::class,
            'handler' => InitHandler::class,
        ],
        Services::CMD_CONFIG_STATUS => [
            'message' => StatusMessage::class,
            'handler' => StatusHandler::class,
        ],
        Services::CMD_REPOSITORY_CREATE => [
            'message' => CreateMessage::class,
            'handler' => CreateHandler::class,
        ],
        Services::CMD_REPOSITORY_LATEST => [
            'message' => RepositoryLatestCommand::class,
            'handler' => RepositoryLatestHandler::class,
        ],
        Services::CMD_REPOSITORY_LIST => [
            'message' => ListMessage::class,
            'handler' => ListHandler::class,
        ],
        Services::CMD_STORAGE_LATEST => [
            'message' => StorageLatestCommand::class,
            'handler' => StorageLatestHandler::class,
        ],
        Services::CMD_TIMELINE_EXECUTE => [
            'message' => ExecuteMessage::class,
            'handler' => ExecuteHandler::class,
        ],
        Services::CMD_TIMELINE_MIGRATE => [
            'message' => MigrateMessage::class,
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
            $container->add($alias, function (Container $container, $config) {
                /** @var MessageFactoryInterface $factory */
                $factory = !empty($config['factory']) ? $config['factory'] : DefaultFactory::class;
                $factory = $container->get($factory);
                if (!$factory instanceof MessageFactoryInterface) {
                    throw new CliException(sprintf(
                        'Expected factory to be an instance of "%s". Got "%s" instead.',
                        MessageFactoryInterface::class,
                        is_object($factory) ? get_class($factory) : gettype($factory)
                    ));
                }
                return $factory->create($config['message']);
            })->withArguments([ContainerInterface::class, $config]);
        }

        // setup the command bus to know which handler to use for each message class
        $container->singleton(Services::COMMAND_BUS, function () use ($commands) {
            $map = [];
            foreach ($commands as $alias => $config) {
                $message = $config['message'];
                $handler = $config['handler'];
                $map[$message] = new $handler();
            }

            return QuickStart::create($map);
        });

        // create a service (that's just an array) that has a list of all the commands for the app
        $container->add(Services::COMMANDS, function (ContainerInterface $container) use ($commands) {
            $commandList = [];
            foreach ($commands as $alias => $config) {
                $commandList[] = new BaseCommand($container, $alias, $config['message']);
            }

            return $commandList;
        })->withArgument(ContainerInterface::class);
    }
}
