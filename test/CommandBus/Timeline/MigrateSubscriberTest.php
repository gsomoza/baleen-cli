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

namespace BaleenTest\Baleen\CommandBus\Timeline;

use Baleen\Cli\CommandBus\Timeline\Migrate\MigrateMessage;
use Baleen\Cli\CommandBus\Timeline\Migrate\MigrateSubscriber;
use Baleen\Migrations\Migration\MigrationInterface;
use Baleen\Migrations\Service\Runner\Event\Collection\CollectionAfterEvent;
use Baleen\Migrations\Service\Runner\Event\Collection\CollectionBeforeEvent;
use Baleen\Migrations\Service\Runner\Event\Collection\CollectionEvent;
use Baleen\Migrations\Service\Runner\Event\Migration\MigrateAfterEvent;
use Baleen\Migrations\Service\Runner\Event\Migration\MigrateBeforeEvent;
use Baleen\Migrations\Version\Version;
use Baleen\Migrations\Version\VersionInterface;
use BaleenTest\Cli\BaseTestCase;
use Mockery as m;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MigrateSubscriberTest
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class MigrateSubscriberTest extends BaseTestCase
{
    /** @var MigrateMessage|m\Mock */
    private $command;

    /** @var OutputInterface|m\Mock */
    private $output;

    /**
     * setUp
     */
    public function setUp()
    {
        $this->command = m::mock(MigrateMessage::class)->shouldAllowMockingProtectedMethods()->makePartial();
        $this->output = m::mock(OutputInterface::class);
        $this->command->setOutput($this->output);
    }

    /**
     * testOnCollectionAfter
     * @param $withProgress
     * @dataProvider trueFalseProvider
     */
    public function testOnCollectionAfter($withProgress)
    {
        /** @var ProgressBar|m\Mock $progress */
        $progress = $withProgress ? m::mock(ProgressBar::class) : null;
        $instance = new MigrateSubscriber($this->command, $progress);

        // now for the actual tests
        if ($withProgress) {
            $progress->shouldReceive('finish')->andReturn('30%');
            $this->output->shouldReceive('writeln')->once()->with('');
        }
        $this->output->shouldReceive('writeln')->with('/END/')->once();

        /** @var CollectionEvent|m\Mock $event */
        $event = m::mock(CollectionEvent::class);
        $instance->onCollectionAfter($event);
    }

    /**
     * testOnMigrationAfter
     *
     * @param $withProgress
     * @param $saveChanges
     *
     * @dataProvider onMigrationAfterProvider
     */
    public function testOnMigrationAfter($withProgress, $saveChanges)
    {
        $this->command->shouldReceive('shouldSaveChanges')->once()->andReturn($saveChanges);

        /** @var ProgressBar|m\Mock|null $progress */
        $progress = $withProgress ? m::mock(ProgressBar::class) : null;
        $instance = new MigrateSubscriber($this->command, $progress);
        $currentProgress = 50;
        /** @var MigrationEvent|m\Mock $event */
        $event = m::mock(MigrationEvent::class);
        if ($withProgress) {
            $progress->shouldReceive('setProgress')->once()->with($currentProgress);
            $event->shouldReceive('getProgress->getCurrent')->andReturn($currentProgress);
        } else {
            $event->shouldNotReceive('getProgress');
        }

        if ($saveChanges) {
            $version = m::mock(VersionInterface::class);
            $event->shouldReceive('getVersion')->once()->andReturn($version);
            $this->command->shouldReceive('getStorage->update')->once()->with($version);
        } else {
            $event->shouldNotReceive('getVersion');
        }
        $instance->onMigrationAfter($event);
    }

    /**
     * onMigrationAfterProvider
     * @return array
     */
    public function onMigrationAfterProvider()
    {
        $trueFalse = [true, false];
        return $this->combinations([$trueFalse, $trueFalse]);
    }

    /**
     * testOnCollectionBefore
     * @param bool $trackProgress
     * @param bool $isDirectionUp
     * @dataProvider onCollectionBeforeProvider
     */
    public function testOnCollectionBefore($trackProgress = true, $isDirectionUp = true)
    {
        /** @var MigrationInterface|m\Mock $migration */
        $migration = m::mock(MigrationInterface::class);
        $target = new Version($migration, false, 'v10');

        /** @var CollectionEvent|m\Mock $event */
        $event = m::mock(CollectionEvent::class);
        $event->shouldReceive([
            'getTarget' => $target,
            'getOptions->isDirectionUp' => $isDirectionUp,
        ])->once();
        $this->output->shouldReceive('writeln')->with('/' . $target->getId() . '/')->once();

        $this->command->shouldReceive('shouldTrackProgress')->once()->andReturn($trackProgress);

        if ($trackProgress) {
            $event->shouldReceive('getProgress->getTotal')->atLeast(1)->andReturn(10);
            $this->output->shouldReceive('isDecorated')->zeroOrMoreTimes()->andReturn(true);
            $this->output
                ->shouldReceive('getVerbosity')
                ->zeroOrMoreTimes()
                ->andReturn(OutputInterface::VERBOSITY_NORMAL);
        } else {
            $event->shouldNotReceive('getProgress');
        }

        $instance = new MigrateSubscriber($this->command);
        $instance->onCollectionBefore($event);
    }

    /**
     * onCollectionBeforeProvider
     * @return array
     */
    public function onCollectionBeforeProvider()
    {
        $trueFalse = [true, false];
        return $this->combinations([$trueFalse, $trueFalse]);
    }

    /**
     * testOnMigrationBefore
     *
     * @dataProvider trueFalseProvider
     *
     * @param $withProgress
     */
    public function testOnMigrationBefore($withProgress)
    {
        /** @var ProgressBar|m\Mock|null $progress */
        $progress = $withProgress ? m::mock(ProgressBar::class) : null;
        $instance = new MigrateSubscriber($this->command, $progress);

        /** @var MigrationEvent|m\Mock $event */
        $event = m::mock(MigrationEvent::class);

        if (!$withProgress) {
            $version = m::mock(VersionInterface::class);
            $event->shouldReceive([
                'getVersion' => $version,
                'getOptions->getDirection' => 'up',
            ])->once();
            $this->command
                ->shouldReceive('getCliCommand->getHelper->formatVersion')
                ->once()
                ->with($version)
                ->andReturn('v123');
            $this->output->shouldReceive('writeln')->with('/UP.*?v123/')->once();
        } else {
            $event->shouldNotReceive('getVersion');
        }
        $instance->onMigrationBefore($event);
    }

    /**
     * testGetSubscribedEvents
     */
    public function testGetSubscribedEvents()
    {
        $keys = [
            CollectionBeforeEvent::class,
            CollectionAfterEvent::class,
            MigrateBeforeEvent::class,
            MigrateAfterEvent::class,
        ];
        $result = (new MigrateSubscriber($this->command))->getSubscribedEvents();
        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $result);
        }
    }
}
