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

namespace Baleen\Cli\Container;

/**
 * This interface contains constants for the names of services in the Service Container. Its useful in order to:
 * A) reduce the coupling between classes for service providers (since they can use a single interface to reference
 * services) and B) provide an easy way to override certain services for libraries that use Baleen CLI as their
 * framework.
 */
interface Services
{
    // ConfigProvider
    const CONFIG = 'config';
    const CONFIG_STORAGE = 'config-storage';
    const BALEEN_BASE_DIR = 'baleen.base_dir';

    // CommandsProvider
    const COMMAND_BUS = 'commands.bus';
    const COMMANDS = 'commands';
    const CMD_CONFIG_INIT = 'commands.config.init';
    const CMD_TIMELINE_EXECUTE = 'commands.timeline.execute';
    const CMD_TIMELINE_MIGRATE = 'commands.timeline.migrate';
    const CMD_REPOSITORY_CREATE = 'commands.repository.create';
    const CMD_REPOSITORY_LATEST = 'commands.repository.latest';
    const CMD_REPOSITORY_LIST = 'commands.repository.list';
    const CMD_STORAGE_LATEST = 'commands.storage.latest';

    // DefaultProvider
    const APPLICATION = 'application';
    const AUTOLOADER = 'autoloader';

    // HelperSetProvider
    const HELPERSET = 'helperset';
    const HELPERSET_QUESTION = 'helperset.question';
    const HELPERSET_CONFIG = 'helperset.config';

    // RepositoryProvider
    const REPOSITORY = 'repository';
    const REPOSITORY_FILESYSTEM = 'repository.filesystem';
    const MIGRATION_FACTORY = 'repository.migration.factory';

    // Storage Provider
    const STORAGE = 'storage';

    // TimelineProvider
    const TIMELINE = 'timeline';
    const TIMELINE_COMPARATOR = 'timeline.comparator';
}
