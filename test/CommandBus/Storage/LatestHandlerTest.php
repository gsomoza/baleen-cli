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
namespace BaleenTest\Cli\CommandBus\Storage;

use Baleen\Cli\CommandBus\Storage\Latest\LatestHandler;
use Baleen\Cli\CommandBus\Storage\Latest\LatestMessage;
use Baleen\Cli\Helper\VersionFormatterInterface;
use Baleen\Migrations\Version;
use Baleen\Migrations\Version\Collection\Migrated;
use BaleenTest\Cli\CommandBus\HandlerTestCase;
use Mockery as m;

/**
 * Class LatestHandlerTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class LatestHandlerTest extends HandlerTestCase
{
    /**
     * setUp
     */
    public function setUp()
    {
        $this->instance = m::mock(\Baleen\Cli\CommandBus\Storage\Latest\LatestHandler::class)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();
        $this->command = m::mock(LatestMessage::class);
        parent::setUp();
    }

    /**
     * testHandle
     *
     * @param $count
     * @dataProvider handleProvider
     */
    public function testHandle($count)
    {
        $this->markTestSkipped('finish implementing');
        /** @var Migrated|m\Mock $migrated */
        $migrated = m::mock(Migrated::class);
        $migrated->shouldReceive('count')->once()->andReturn($count);
        $this->command->shouldReceive('getStorage->fetchAll')->once()->andReturn($migrated);

        $line = m::type('string');
        if ($count > 0) {
            $last = new Version('v123', true);
            $migrated->shouldReceive('last')->andReturn($last);
            /** @var VersionFormatterInterface|m\Mock $formatter */
            $formatter = m::mock(VersionFormatterInterface::class);
            $formatter->shouldReceive('formatVersion')->once()->with($last)->andReturn('v123');
            $this->command
                ->shouldReceive('getCliCommand->getHelper')
                ->once()
                ->with('versionFormatter')
                ->andReturn($formatter);
            $line = '/v123/';
        }
        $this->output->shouldReceive('writeln')->with($line)->once();

        $this->handle();
    }

    /**
     * handleProvider
     * @return array
     */
    public function handleProvider()
    {
        return [
            [0], [99]
        ];
    }
}
