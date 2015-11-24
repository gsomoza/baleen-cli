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
 * <http://www.doctrine-project.org>.
 */

namespace Baleen\Cli\Provider;

use Baleen\Cli\Application;
use Baleen\Cli\CommandBus\CliBus;
use Baleen\Cli\CommandBus\Migration\Create\CreateMessage;
use Baleen\Cli\CommandBus\Migration\Latest\LatestMessage;
use Baleen\Cli\CommandBus\Migration\Listing\ListMessage;
use Baleen\Cli\CommandBus\Run\Execute\ExecuteMessage;
use Baleen\Cli\CommandBus\Storage\Latest\LatestMessage as StorageLatestMessage;
use Baleen\Cli\Config\ConfigInterface;
use Baleen\Cli\Config\ConfigStorage;
use Baleen\Cli\Repository\MigrationRepositoriesServiceInterface;
use Baleen\Migrations\Migration\Factory\FactoryInterface;
use Baleen\Migrations\Service\Runner\MigrationRunner;
use Baleen\Migrations\Shared\Event\PublisherInterface;
use Baleen\Migrations\Version\Collection\Resolver\ResolverInterface;
use Baleen\Migrations\Version\Comparator\ComparatorInterface;
use Baleen\Migrations\Version\Repository\VersionRepositoryInterface;
use Composer\Autoload\ClassLoader;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This interface contains constants for the names of services in the Service Container. Its useful in order to:
 * A) reduce the coupling between classes for service providers (since they can use a single interface to reference
 * services) and B) provide an easy way to override certain services for libraries that use Baleen CLI as their
 * framework.
 */
interface Services
{
    // ConfigProvider
    /** Reference to the Config service */
    const CONFIG = ConfigInterface::class;
    /** Reference to the ConfigStorage service */
    const CONFIG_STORAGE = ConfigStorage::class;
    /** Reference to Baleen CLI's base directory */
    const BALEEN_BASE_DIR = 'baleen.base_dir';

    // CommandsProvider
    /** Reference to the domain's CommandBus service */
    const COMMAND_BUS = CliBus::class;
    /** Reference to an array of available commands */
    const COMMANDS = 'commands';
    /** Reference to the config:init command */
    const CMD_CONFIG_INIT = 'commands.config.init';
    /** Reference to the config:status command */
    const CMD_CONFIG_STATUS = 'commands.config.status';
    /** Reference to the run:execute command */
    const CMD_RUN_EXECUTE = ExecuteMessage::class;
    /** Reference to the timelien:migrate command */
    const CMD_RUN_MIGRATE = 'commands.timeline.migrate';
    /** Reference to the repository:create command */
    const CMD_REPOSITORY_CREATE = CreateMessage::class;
    /** Reference to the repository:latest command */
    const CMD_REPOSITORY_LATEST = LatestMessage::class;
    /** Reference to the repository:list command */
    const CMD_MIGRATIONS_LIST = ListMessage::class;
    /** Reference to the storage:latest command */
    const CMD_STORAGE_LATEST = StorageLatestMessage::class;

    // ApplicationProvider
    /** Reference to the Symfony Console Application instance */
    const APPLICATION = Application::class;
    /** Reference to a Symfony Event Dispatcher to be attached to the application */
    const APPLICATION_DISPATCHER = EventDispatcherInterface::class;
    /** Reference to the Composer autoloader */
    const AUTOLOADER = ClassLoader::class;

    // HelperSetProvider
    /** Reference to the Symfony Console HelperSet to be used for the Application */
    const HELPERSET = HelperSet::class;
    /** Reference to the Question console helper */
    const HELPERSET_QUESTION = 'helperset.question';
    /** Reference to the Config console helper */
    const HELPERSET_CONFIG = 'helperset.config';

    // MigrationRepositoryProvider
    /** Reference to the Repository service */
    const MIGRATION_REPOSITORY = MigrationRepositoriesServiceInterface::class;
    /** Reference to the Filesystem to be used for the Repository service */
    const MIGRATION_REPOSITORY_FILESYSTEM = FilesystemInterface::class;
    /** Reference to the factory to be used to instantiate Migrations */
    const MIGRATION_FACTORY = FactoryInterface::class;

    // Storage Provider
    /** Reference to the Storage service */
    const VERSION_REPOSITORY = VersionRepositoryInterface::class;

    /** Reference to the Comparator service */
    const COMPARATOR = ComparatorInterface::class;
    /** Reference to a collection resolver service */
    const RESOLVER = ResolverInterface::class;

    // Domain Servics
    /** Reference to the event publisher service */
    const PUBLISHER = PublisherInterface::class;
    /** Reference to the Migration Runner service */
    const MIGRATION_RUNNER = MigrationRunner::class;
}
