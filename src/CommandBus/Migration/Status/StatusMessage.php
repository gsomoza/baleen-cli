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

namespace Baleen\Cli\CommandBus\Migration\Status;

use Baleen\Cli\CommandBus\Migration\AbstractMigrationMessage;
use Baleen\Cli\CommandBus\Util\ComparatorAwareTrait;
use Baleen\Cli\CommandBus\Util\RepositoriesAwareTrait;
use Baleen\Cli\CommandBus\Util\StorageAwareTrait;
use Baleen\Cli\Config\ConfigInterface;
use Baleen\Cli\Config\ConfigStorage;
use Baleen\Cli\Repository\MigrationRepositoriesServiceInterface;
use Baleen\Migrations\Version\Comparator\ComparatorInterface;
use Baleen\Migrations\Version\Repository\VersionRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

/**
 * Message class for the config:status command.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class StatusMessage extends AbstractMigrationMessage implements StatusMessageInterface
{
    use StorageAwareTrait;
    use ComparatorAwareTrait;

    const OPTION_REPOSITORY = 'repository';

    /**
     * StatusMessage constructor.
     *
     * @param MigrationRepositoriesServiceInterface $repositories
     * @param VersionRepositoryInterface $versionRepository
     * @param ComparatorInterface $comparator
     * @param ConfigStorage $configStorage
     * @param ConfigInterface $config
     */
    public function __construct(
        MigrationRepositoriesServiceInterface $repositories,
        VersionRepositoryInterface $versionRepository,
        ComparatorInterface $comparator,
        ConfigStorage $configStorage,
        ConfigInterface $config
    ) {
        $this->setStorage($versionRepository);
        $this->setComparator($comparator);
        parent::__construct($config, $repositories);
    }

    /**
     * Configures a console command by setting name, description, arguments, etc.
     *
     * @param Command $command
     */
    public static function configure(Command $command)
    {
        $command->setName('migrations:status');
        $command->setAliases(['status']);
        $command->setDescription(
            'Shows the current migration status. Shows the status across all available repositories unless the ' .
            '--repository option is provided.'
        );
        $command->addOption(
            self::OPTION_REPOSITORY,
            'r',
            InputOption::VALUE_OPTIONAL,
            'Show the status of only the specified repository.'
        );
    }
}
