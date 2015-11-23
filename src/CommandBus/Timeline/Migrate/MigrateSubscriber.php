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

namespace Baleen\Cli\CommandBus\Timeline\Migrate;

use Baleen\Cli\Helper\VersionFormatter;
use Baleen\Migrations\Service\Runner\Event\Collection\CollectionAfterEvent;
use Baleen\Migrations\Service\Runner\Event\Collection\CollectionBeforeEvent;
use Baleen\Migrations\Service\Runner\Event\Migration\MigrateAfterEvent;
use Baleen\Migrations\Service\Runner\Event\Migration\MigrateBeforeEvent;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zend\EventManager\EventInterface;

/**
 * Class MigrateListener
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
final class MigrateSubscriber implements EventSubscriberInterface
{
    /** @var MigrateMessage */
    private $command;

    /** @var ProgressBar */
    private $progress;

    /**
     * MigrateSubscriber constructor.
     *
     * @param MigrateMessage $command
     * @param ProgressBar $progress
     */
    public function __construct(MigrateMessage $command, ProgressBar $progress = null)
    {
        $this->command = $command;
        $this->progress = $progress;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            CollectionBeforeEvent::class => 'onCollectionBefore',
            CollectionAfterEvent::class => 'onCollectionAfter',
            MigrateBeforeEvent::class => 'onMigrationBefore',
            MigrateAfterEvent::class => 'onMigrationAfter',
        ];
    }

    /**
     * @param MigrateBeforeEvent $event
     */
    public function onMigrationBefore(MigrateBeforeEvent $event)
    {
        if (!$this->progress) {
            $version = $event->getTarget();
            $direction = strtoupper($event->getOptions()->getDirection());
            /** @var VersionFormatter $versionFormatter */
            $versionFormatter = $this->command->getCliCommand()->getHelper('versionFormatter');
            $message = "<info>[$direction]</info> " . $versionFormatter->formatVersion($version);
            $this->command->getOutput()->writeln($message);
        }
    }

    /**
     * onMigrationAfter.
     *
     * @param MigrateAfterEvent $event
     */
    public function onMigrationAfter(MigrateAfterEvent $event)
    {
        if ($this->progress) {
            $runProgress = $event->getContext()->getProgress();
            $this->progress->setProgress($runProgress->getCurrent());
        }
        if ($this->command->shouldSaveChanges()) {
            $target = $event->getTarget();
            $this->command->getStorage()->update($target);
        }
    }

    /**
     * onCollectionBefore.
     *
     * @param CollectionBeforeEvent $event
     */
    public function onCollectionBefore(CollectionBeforeEvent $event)
    {
        $output = $this->command->getOutput();
        if ($event->getCollection()->isEmpty()) {
            $output->writeln('Already up-to-date.');
            return;
        }

        $target = $event->getTarget();

        $output->writeln(sprintf(
            '<info>[START]</info> Migrating %s to <comment>%s</comment>:',
            $event->getOptions()->getDirection()->isUp() ? 'up' : 'down',
            $target->getId()
        ));
        if ($this->command->shouldTrackProgress()) {
            $this->progress = new ProgressBar($output, 1);
            $this->progress->setFormat('verbose');
            $this->progress->setProgress(0);
        }
    }

    /**
     * onCollectionAfter.
     *
     * @param CollectionEvent $event
     */
    public function onCollectionAfter(CollectionEvent $event)
    {
        if ($event->getCollection()->isEmpty()) {
            return;
        }
        $output = $this->command->getOutput();
        if ($this->progress) {
            $this->progress->finish();
            $output->writeln(''); // new line after progress bar
        }
        $output->writeln('<info>[END]</info>');
    }
}
