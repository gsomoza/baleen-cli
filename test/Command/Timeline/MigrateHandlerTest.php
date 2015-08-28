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

namespace BaleenTest\Baleen\Command\Timeline;

use Baleen\Cli\Command\Timeline\MigrateCommand;
use Baleen\Cli\Command\Timeline\MigrateHandler;
use Baleen\Cli\Exception\CliException;
use Baleen\Migrations\Event\EventInterface;
use Baleen\Migrations\Event\Timeline\CollectionEvent;
use Baleen\Migrations\Event\Timeline\MigrationEvent;
use Baleen\Migrations\Migration\Options;
use Baleen\Migrations\Version;
use BaleenTest\Baleen\Command\HandlerTestCase;
use Mockery as m;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Class MigrateHandlerTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class MigrateHandlerTest extends HandlerTestCase
{
    /**
     * setUp
     */
    public function setUp()
    {
        $this->instance = m::mock(MigrateHandler::class)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();
        $this->command = m::mock(MigrateCommand::class)->makePartial();
        parent::setUp();
    }

    /**
     * testHandle
     * @param $verbosity
     * @param $noProgress
     * @param $noStorage
     * @dataProvider executeProvider
     */
    public function testHandle($verbosity, $noProgress, $noStorage)
    {
        // values don't matter here
        $strategy = 'both';
        $target = 'someTarget';
        $dryRun = 'no!'; // again: this really doesn't matter for the test
        $shouldTrackProgress = $verbosity !== OutputInterface::VERBOSITY_QUIET;

        $dispatcher = m::mock(EventDispatcher::class);
        $this->command->shouldReceive('getTimeline->getEventDispatcher')->once()->andReturn($dispatcher);

        $this->output->shouldReceive('getVerbosity')->once()->andReturn($verbosity);
        if ($shouldTrackProgress) {
            $this->input->shouldReceive('getOption')
                ->once()
                ->with(MigrateCommand::OPT_NOPROGRESS)
                ->andReturn($noProgress);
            $shouldTrackProgress = !$noProgress;
        }

        $this->input->shouldReceive('getArgument')->with(MigrateCommand::ARG_TARGET)->once()->andReturn($target);
        $this->input->shouldReceive('getOption')->with(MigrateCommand::OPT_DRY_RUN)->once()->andReturn($dryRun);
        $this->input->shouldReceive('getOption')->with(MigrateCommand::OPT_NO_STORAGE)->once()->andReturn($noStorage);
        $this->instance->shouldReceive('getStrategyOption')->with($this->input)->andReturn($strategy);
        $this->instance->shouldReceive('attachEvents')->once()->with($this->output, $dispatcher);
        $this->command->shouldReceive('getTimeline->' . $strategy)->once()->with($target, m::type(Options::class));

        $this->handle();

        $this->assertEquals($shouldTrackProgress, $this->getPropVal('trackProgress', $this->instance));
        $this->assertEquals(!$noStorage, $this->getPropVal('saveChanges', $this->instance));
    }

    /**
     * handleProvider
     * @return array
     */
    public function executeProvider()
    {
        $verbosities = [
            OutputInterface::VERBOSITY_NORMAL,
            OutputInterface::VERBOSITY_QUIET,
            OutputInterface::VERBOSITY_VERBOSE,
            OutputInterface::VERBOSITY_VERY_VERBOSE,
            OutputInterface::VERBOSITY_DEBUG,
        ];
        $trueFalse = [true, false];
        return $this->combinations([$verbosities, $trueFalse, $trueFalse]);
    }

    /**
     * testGetStrategyOption
     * @dataProvider getStrategyOptionProvider
     */
    public function testGetStrategyOption($strategy, $throwException = false)
    {
        $this->input->shouldReceive('getOption')->once()->with(MigrateCommand::OPT_STRATEGY)->andReturn($strategy);

        if ($throwException) {
            $this->setExpectedException(CliException::class, 'Unknown');
        }

        $this->invokeMethod('getStrategyOption', $this->instance, [$this->input]);
    }

    /**
     * getStrategyOptionProvider
     * @return array
     */
    public function getStrategyOptionProvider()
    {
        return [
            ['both'],
            ['up'],
            ['down'],
            ['oops!', true],
        ];
    }

    /**
     * testOnCollectionAfter
     * @param $withProgress
     * @dataProvider trueFalseProvider
     */
    public function testOnCollectionAfter($withProgress)
    {
        $this->output->shouldReceive('writeln')->with('/END/')->once();
        if ($withProgress) {
            $progress = m::mock();
            $progress->shouldReceive('finish')->once();
            $this->setPropVal('progress', $progress, $this->instance);
            $this->output->shouldReceive('writeln')->once();
        }
        $this->setPropVal('output', $this->output, $this->instance);
        $this->invokeMethod('onCollectionAfter', $this->instance);
    }

    /**
     * testOnMigrationAfter
     * @param $withProgress
     * @dataProvider trueFalseProvider
     */
    public function testOnMigrationAfter($withProgress)
    {
        $currentProgress = 50;
        $event = m::mock(MigrationEvent::class);
        if ($withProgress) {
            $progress = m::mock();
            $progress->shouldReceive('setProgress')->once()->with($currentProgress);
            $this->setPropVal('progress', $progress, $this->instance);

            $event->shouldReceive('getProgress->getCurrent')->andReturn($currentProgress);
        } else {
            $event->shouldNotReceive('getProgress');
        }
        $this->invokeMethod('onMigrationAfter', $this->instance, [$event]);
    }

    /**
     * testSaveVersionListener
     */
    public function testSaveVersionListener()
    {
        $version = new Version(1); // value doesn't really matter
        $event = m::mock(MigrationEvent::class);
        $event->shouldReceive('getVersion')->once()->andReturn($version);

        $command = m::mock(Command::class);
        $command->shouldReceive('getStorage->update')->once()->with($version);
        $this->setPropVal('command', $command, $this->instance);

        $this->invokeMethod('saveVersionListener', $this->instance, [$event]);
    }

    /**
     * testOnCollectionBefore
     * @param bool $trackProgress
     * @param bool $isDirectionUp
     * @dataProvider onCollectionBeforeProvider
     */
    public function testOnCollectionBefore($trackProgress = true, $isDirectionUp = true)
    {
        $target = new Version('v10');
        /** @var m\Mock|CollectionEvent $event */
        $event = m::mock(CollectionEvent::class);
        $event->shouldReceive([
            'getTarget' => $target,
            'getOptions->isDirectionUp' => $isDirectionUp,
        ])->once();
        $this->output->shouldReceive('writeln')->with('/' . $target->getId() . '/')->once();
        $this->setPropVal('output', $this->output, $this->instance);

        $this->setPropVal('trackProgress', $trackProgress, $this->instance);
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

        $this->invokeMethod('onCollectionBefore', $this->instance, [$event]);
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
     */
    public function testOnMigrationBefore()
    {
        $version = new Version('v10');
        $event = m::mock(MigrationEvent::class);
        $event->shouldReceive([
            'getVersion' => $version,
            'getOptions' => new Options(Options::DIRECTION_UP),
        ])->once();
        $this->output->shouldReceive('writeln')->with('/' . $version->getId() . '/')->once();
        $this->setPropVal('output', $this->output, $this->instance);
        $this->invokeMethod('onMigrationBefore', $this->instance, [$event]);
    }

    /**
     * testAttachEvents
     * @param int $verbosity
     * @param $saveChanges
     * @dataProvider attachEventsProvider
     */
    public function testAttachEvents($verbosity, $saveChanges)
    {
        $dispatcher = m::mock(EventDispatcher::class);
        $this->setPropVal('saveChanges', $saveChanges, $this->instance);
        $this->output->shouldReceive('getVerbosity')->andReturn($verbosity);
        $counts = [
            EventInterface::MIGRATION_BEFORE => 0,
            EventInterface::MIGRATION_AFTER => 0,
            EventInterface::COLLECTION_BEFORE => 0,
            EventInterface::COLLECTION_AFTER => 0,
        ];
        if ($verbosity >= OutputInterface::VERBOSITY_NORMAL) {
            $counts = [
                EventInterface::MIGRATION_BEFORE => 1,
                EventInterface::MIGRATION_AFTER => 1,
                EventInterface::COLLECTION_BEFORE => 1,
                EventInterface::COLLECTION_AFTER => 1,
            ];
        }
        if ($saveChanges) {
            $counts[EventInterface::MIGRATION_AFTER] += 1;
        }
        foreach ($counts as $event => $count) {
            $dispatcher->shouldReceive('addListener')->times($count)->with($event, m::any());
        }
        $this->invokeMethod('attachEvents', $this->instance, [$this->output, $dispatcher]);
    }

    /**
     * attachEventsProvider
     * @return array
     */
    public function attachEventsProvider()
    {
        $trueFalse = [true, false];
        $verbosities = [
            OutputInterface::VERBOSITY_QUIET,
            OutputInterface::OUTPUT_NORMAL,
            OutputInterface::VERBOSITY_VERY_VERBOSE,
        ];
        return $this->combinations([$verbosities, $trueFalse]);
    }
}
