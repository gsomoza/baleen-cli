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

namespace BaleenTest\Cli\CommandBus\Timeline;

use Baleen\Cli\CommandBus\Timeline\Migrate\MigrateHandler;
use Baleen\Cli\CommandBus\Timeline\Migrate\MigrateMessage;
use Baleen\Migrations\Migration\Options;
use Baleen\Migrations\Version;
use BaleenTest\Cli\CommandBus\HandlerTestCase;
use Mockery as m;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

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
        $this->command = m::mock(\Baleen\Cli\CommandBus\Timeline\Migrate\MigrateMessage::class)->makePartial();
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
        $dryRun = true;

        $this->command->shouldReceive('getTimelineFactory->getEventDispatcher->addSubscriber')
            ->with(m::type(EventSubscriberInterface::class))
            ->once();

        $this->command->shouldReceive('isDryRun')->once()->andReturn($dryRun);
        $this->command->shouldReceive('getStrategy')->once()->andReturn($strategy);
        $this->command->shouldReceive('getTarget')->once()->andReturn($target);
        $this->command->shouldReceive('getTimelineFactory->' . $strategy)->once()->with($target, m::type(Options::class));

        $this->handle();
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
}
