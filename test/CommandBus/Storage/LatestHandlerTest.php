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

use Baleen\Cli\CommandBus\Storage\LatestMessage;
use Baleen\Cli\CommandBus\Storage\LatestHandler;
use Baleen\Cli\Exception\CliException;
use Baleen\Migrations\Version;
use Baleen\Migrations\Version\Collection\MigratedVersions;
use Baleen\Migrations\Version\Comparator\DefaultComparator;
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
        $this->instance = m::mock(LatestHandler::class)
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
        $migrated = m::mock(MigratedVersions::class);
        $migrated->shouldReceive('count')->once()->andReturn($count);
        if ($count > 0) {
            $last = 'v123';
            $migrated->shouldReceive('last->getId')->andReturn($last);
            $this->output->shouldReceive('writeln')->with($last)->once();
        } else {
            $this->output->shouldReceive('writeln')->with(m::type('string'))->once();
        }
        $this->command->shouldReceive('getStorage->fetchAll')->once()->andReturn($migrated);
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
