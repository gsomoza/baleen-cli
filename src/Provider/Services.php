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
    const CONFIG = 'config';
    /** Reference to the ConfigStorage service */
    const CONFIG_STORAGE = 'config-storage';
    /** Reference to Baleen CLI's base directory */
    const BALEEN_BASE_DIR = 'baleen.base_dir';

    // CommandsProvider
    /** Reference to the CommandBus service */
    const COMMAND_BUS = 'commands.bus';
    /** Reference to an array of available commands */
    const COMMANDS = 'commands';
    /** Reference to the config:init command */
    const CMD_CONFIG_INIT = 'commands.config.init';
    /** Reference to the config:status command */
    const CMD_CONFIG_STATUS = 'commands.config.status';
    /** Reference to the timeline:execute command */
    const CMD_TIMELINE_EXECUTE = 'commands.timeline.execute';
    /** Reference to the timelien:migrate command */
    const CMD_TIMELINE_MIGRATE = 'commands.timeline.migrate';
    /** Reference to the repository:create command */
    const CMD_REPOSITORY_CREATE = 'commands.repository.create';
    /** Reference to the repository:latest command */
    const CMD_REPOSITORY_LATEST = 'commands.repository.latest';
    /** Reference to the repository:list command */
    const CMD_REPOSITORY_LIST = 'commands.repository.list';
    /** Reference to the storage:latest command */
    const CMD_STORAGE_LATEST = 'commands.storage.latest';

    // ApplicationProvider
    /** Reference to the Symfony Console Application instance */
    const APPLICATION = 'application';
    /** Reference to a Symfony Event Dispatcher to be attached to the application */
    const APPLICATION_DISPATCHER = 'application.dispatcher';
    /** Reference to the Composer autoloader */
    const AUTOLOADER = 'autoloader';

    // HelperSetProvider
    /** Reference to the Symfony Console HelperSet to be used for the Application */
    const HELPERSET = 'helperset';
    /** Reference to the Question console helper */
    const HELPERSET_QUESTION = 'helperset.question';
    /** Reference to the Config console helper */
    const HELPERSET_CONFIG = 'helperset.config';

    // RepositoryProvider
    /** Reference to the Repository service */
    const REPOSITORY = 'repository';
    /** Reference to the Filesystem to be used for the Repository service */
    const REPOSITORY_FILESYSTEM = 'repository.filesystem';
    /** Reference to the factory to be used to instantiate Migrations */
    const MIGRATION_FACTORY = 'repository.migration.factory';

    // Storage Provider
    /** Reference to the Storage service */
    const STORAGE = 'storage';

    // TimelineProvider
    /** Reference to the Timeline service */
    const TIMELINE = 'timeline';
    /** Reference to the Comparator service */
    const COMPARATOR = 'comparator';
}
