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

namespace Baleen\Cli\CommandBus\Timeline;

use Baleen\Cli\Exception\CliException;
use Baleen\Migrations\Event\EventInterface;
use Baleen\Migrations\Event\Timeline\CollectionEvent;
use Baleen\Migrations\Event\Timeline\MigrationEvent;
use Baleen\Migrations\Migration\Options;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class MigrateHandler.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class MigrateHandler
{
    /** @var MigrateMessage */
    private $command;

    /**
     * handle.
     *
     * @param MigrateMessage $command
     *
     * @throws CliException
     */
    public function handle(MigrateMessage $command)
    {
        $this->command = $command;

        $options = new Options(
            Options::DIRECTION_UP,  // this value will get replaced by timeline later
            false,
            $command->isDryRun(),
            false
        );

        $command->getTimeline()->getEventDispatcher()->addSubscriber(
            new MigrateSubscriber($command)
        );

        $strategy = $command->getStrategy();

        $command->getTimeline()->$strategy(
            $command->getTarget(),
            $options
        );
    }

    /**
     * @inheritDoc
     */
    protected function attachEvents(EventDispatcherInterface $dispatcher)
    {
        $output = $this->command->getOutput();

        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_NORMAL) {
            $dispatcher->addListener(EventInterface::COLLECTION_BEFORE, [$this, 'onCollectionBefore']);
            $dispatcher->addListener(EventInterface::COLLECTION_AFTER, [$this, 'onCollectionAfter']);
            $dispatcher->addListener(EventInterface::MIGRATION_BEFORE, [$this, 'onMigrationBefore']);
            $dispatcher->addListener(EventInterface::MIGRATION_AFTER, [$this, 'onMigrationAfter']);
        }

        if ($this->command->shouldSaveChanges()) {
            $dispatcher->addListener(EventInterface::MIGRATION_AFTER, [$this, 'saveVersionListener']);
        }
    }
}
