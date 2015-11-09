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

use Baleen\Cli\CommandBus\Timeline\ExecuteMessage;
use Baleen\Cli\CommandBus\Timeline\ExecuteHandler;
use Baleen\Migrations\Migration\Options;
use Baleen\Migrations\Timeline;
use Baleen\Migrations\Timeline\TimelineInterface;
use Baleen\Migrations\Version;
use BaleenTest\Cli\CommandBus\HandlerTestCase;
use Mockery as m;

/**
 * Class ExecuteHandlerTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class ExecuteHandlerTest extends HandlerTestCase
{
    /**
     * setUp
     */
    public function setUp()
    {
        $this->instance = m::mock(ExecuteHandler::class)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();
        $this->command = m::mock(ExecuteMessage::class)->makePartial();
        parent::setUp();
    }

    /**
     * testHandle
     * @param $isInteractive
     * @param $isUp
     * @param $isDryRun
     * @param $askResult
     * @dataProvider executeProvider
     */
    public function testHandle($isInteractive, $isUp, $isDryRun, $askResult, $executeResult)
    {
        $versionId = '123';
        $this->input->shouldReceive('isInteractive')->once()->andReturn($isInteractive);
        $this->input->shouldReceive('getArgument')->with(ExecuteMessage::ARG_VERSION)->once()->andReturn($versionId);
        $this->input->shouldReceive('getArgument')->with(ExecuteMessage::ARG_DIRECTION)->once()->andReturn(!$isUp);
        $this->input->shouldReceive('getOption')->with(ExecuteMessage::OPT_DRY_RUN)->once()->andReturn($isDryRun);

        /** @var m\Mock|TimelineInterface $timeline */
        $timeline = m::mock(TimelineInterface::class);
        $timeline->shouldReceive('getVersions->get')->once()->with($versionId)->andReturn(new Version($versionId));
        $this->command->shouldReceive('getTimeline')->once()->andReturn($timeline);

        if ($isInteractive) {
            $this->output->shouldReceive('writeln')->once()->with('/WARNING/');
            $this->assertQuestionAsked($askResult, m::type('Symfony\Component\Console\Question\ConfirmationQuestion'));
        }

        if (!$isInteractive || $askResult) {
            $timeline->shouldReceive('runSingle')->with(
                m::on(function ($version) {
                    return (string) $version === '123';
                }),
                m::on(function (Options $options) use ($isUp, $isDryRun) {
                    return $options->isDryRun() === $isDryRun
                    && $options->isDirectionUp() === $isUp;
                })
            )->once()->andReturn($executeResult);
            if ($executeResult && !$isDryRun) {
                $this->command->shouldReceive('getStorage->update')->with($executeResult)->once();
            }
            $this->output->shouldReceive('writeln')->once()->with('/successfully/');
        }

        $this->handle();
    }

    /**
     * @return array
     */
    public function executeProvider()
    {
        return $this->combinations([
            [true, false], // isInteractive
            [true, false], // isUp
            [true, false], // isDryRun
            [true, false], // askResult
            [new Version(1), false, null] // executeResult
        ]);
    }

}
