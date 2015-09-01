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

namespace Baleen\Cli\Command\Timeline;

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
    /** @var ProgressBar */
    protected $progress;

    /** @var bool */
    protected $saveChanges = true;

    /** @var array */
    protected $strategies = [
        Options::DIRECTION_UP => 'upTowards',
        Options::DIRECTION_DOWN => 'downTowards',
        'both' => 'goTowards',
    ];

    /** @var bool */
    protected $trackProgress = true;

    /** @var string */
    protected $directionPhrase = 'Migrating to';

    /** @var OutputInterface */
    protected $output;

    /** @var MigrateCommand */
    protected $command;

    /**
     * handle.
     *
     * @param MigrateCommand $command
     *
     * @throws CliException
     */
    public function handle(MigrateCommand $command)
    {
        $input = $command->getInput();
        $output = $command->getOutput();
        $this->command = $command;

        $targetArg = $input->getArgument(MigrateCommand::ARG_TARGET);
        $strategy = $this->getStrategyOption($input);

        $options = new Options(Options::DIRECTION_UP); // this value will get replaced by timeline later
        $options->setDryRun($input->getOption(MigrateCommand::OPT_DRY_RUN));
        $options->setExceptionOnSkip(false);

        $this->saveChanges = !$input->getOption(MigrateCommand::OPT_NO_STORAGE) && !$options->isDryRun();

        $this->trackProgress = ($output->getVerbosity() !== OutputInterface::VERBOSITY_QUIET)
            && !$input->getOption(MigrateCommand::OPT_NOPROGRESS);

        $this->attachEvents($output, $command->getTimeline()->getEventDispatcher());

        /* @var \Baleen\Migrations\Version\Collection\LinkedVersions $results */
        $command->getTimeline()->$strategy($targetArg, $options);
    }

    /**
     * @inheritDoc
     */
    protected function attachEvents(OutputInterface $output, EventDispatcherInterface $dispatcher)
    {
        $this->output = $output;

        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_NORMAL) {
            $dispatcher->addListener(EventInterface::COLLECTION_BEFORE, [$this, 'onCollectionBefore']);
            $dispatcher->addListener(EventInterface::COLLECTION_AFTER, [$this, 'onCollectionAfter']);
            $dispatcher->addListener(EventInterface::MIGRATION_BEFORE, [$this, 'onMigrationBefore']);
            $dispatcher->addListener(EventInterface::MIGRATION_AFTER, [$this, 'onMigrationAfter']);
        }

        if ($this->saveChanges) {
            $dispatcher->addListener(EventInterface::MIGRATION_AFTER, [$this, 'saveVersionListener']);
        }
    }

    /**
     * saveVersionListener.
     *
     * @param MigrationEvent $event
     */
    public function saveVersionListener(MigrationEvent $event)
    {
        $version = $event->getVersion();
        $this->command->getStorage()->update($version);
    }

    /**
     * @param MigrationEvent $event
     */
    public function onMigrationBefore(MigrationEvent $event)
    {
        if (!$this->progress) {
            $version = $event->getVersion();
            $this->output->writeln(sprintf(
                '<info>[%s]</info> <comment>%s</comment>',
                $version->getId(),
                strtoupper($event->getOptions()->getDirection())
            ));
        }
    }

    /**
     * onMigrationAfter.
     *
     * @param MigrationEvent $event
     */
    public function onMigrationAfter(MigrationEvent $event)
    {
        if ($this->progress) {
            $runProgress = $event->getProgress();
            $this->progress->setProgress($runProgress->getCurrent());
        }
    }

    /**
     * onCollectionBefore.
     *
     * @param CollectionEvent $event
     */
    public function onCollectionBefore(CollectionEvent $event)
    {
        $target = $event->getTarget();

        $this->output->writeln(sprintf(
            '<info>[START]</info> Migrating %s to <comment>%s</comment>:',
            $event->getOptions()->isDirectionUp() ? 'up' : 'down',
            $target->getId()
        ));
        if ($this->trackProgress) {
            $this->progress = new ProgressBar($this->output, $event->getProgress()->getTotal());
            $this->progress->setFormat('verbose');
            $this->progress->setProgress(0);
        }
    }

    /**
     * onCollectionAfter.
     */
    public function onCollectionAfter()
    {
        if ($this->progress) {
            $this->progress->finish();
            $this->output->writeln(''); // new line after progress bar
        }
        $this->output->writeln('<info>[END]</info>');
    }

    /**
     * @param InputInterface $input
     *
     * @return string
     *
     * @throws CliException
     */
    protected function getStrategyOption(InputInterface $input)
    {
        $strategy = strtolower($input->getOption(MigrateCommand::OPT_STRATEGY));
        if (!isset($this->strategies[$strategy])) {
            throw new CliException(sprintf(
                'Unknown strategy "%s". Must be one of: %s',
                $strategy,
                implode(', ', array_keys($this->strategies))
            ));
        }

        return $this->strategies[$strategy];
    }
}
