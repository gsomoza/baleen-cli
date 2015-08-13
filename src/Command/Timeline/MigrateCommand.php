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
use Baleen\Migrations\Timeline;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MigrateCommand.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class MigrateCommand extends AbstractTimelineCommand
{
    const COMMAND_NAME = self::COMMAND_ALIAS;
    const COMMAND_ALIAS = 'migrate';
    const ARG_TARGET = 'target';
    const OPT_STRATEGY = 'strategy';
    const OPT_NOPROGRESS = 'no-progress';

    /** @var OutputInterface */
    protected $output;

    /** @var ProgressBar */
    protected $progress;

    /** @var array */
    protected $strategies = [
        Options::DIRECTION_UP => 'upTowards',
        Options::DIRECTION_DOWN => 'downTowards',
        'both' => 'goTowards',
    ];

    /** @var bool  */
    protected $trackProgress = true;

    /**
     * @inheritdoc
     */
    public function configure()
    {
        parent::configure();

        $this->setDescription('Migrates all versions up to and including the specified target.')
            ->setAliases([self::COMMAND_ALIAS])
            ->addArgument(self::ARG_TARGET, InputArgument::OPTIONAL, 'The target version to migrate to.', 'latest')
            ->addOption(
                self::OPT_NOPROGRESS,
                null,
                InputOption::VALUE_NONE,
                'Show a more detailed log instead of a progress bar.')
            ->addOption(
                self::OPT_STRATEGY,
                's',
                InputOption::VALUE_REQUIRED,
                'Strategy to migrate with (up/down/both).',
                'up'
            );
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $targetArg = $input->getArgument(self::ARG_TARGET);
        $strategy = $this->getStrategyOption($input);

        $options = new Options(Options::DIRECTION_UP); // this value will get replaced by timeline later
        $options->setDryRun($input->getOption(self::OPT_DRY_RUN));

        $this->trackProgress = ($output->getVerbosity() !== OutputInterface::VERBOSITY_QUIET)
                                && !$input->getOption(self::OPT_NOPROGRESS);

        $this->attachEvents($output);

        $this->getTimeline()->$strategy($targetArg, $options);
    }

    /**
     * @inheritDoc
     */
    protected function attachEvents(OutputInterface $output)
    {
        $this->output = $output;
        $dispatcher = $this->getTimeline()->getEventDispatcher();

        if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_NORMAL) {
            $dispatcher->addListener(EventInterface::COLLECTION_BEFORE, [$this, 'onCollectionBefore']);
            $dispatcher->addListener(EventInterface::COLLECTION_AFTER, [$this, 'onCollectionAfter']);
            $dispatcher->addListener(EventInterface::MIGRATION_BEFORE, [$this, 'onMigrationBefore']);
            $dispatcher->addListener(EventInterface::MIGRATION_AFTER, [$this, 'onMigrationAfter']);
        }
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
     * onMigrationAfter
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
            '<info>[START]</info> Migrating towards <comment>%s</comment>:',
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
        $this->output->writeln('<info>[END]</info> All done!');
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
        $strategy = strtolower($input->getOption(self::OPT_STRATEGY));
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
