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
namespace BaleenTest\Baleen\Command\Storage;

use Baleen\Cli\Command\Storage\LatestCommand;
use Baleen\Cli\Command\Storage\LatestHandler;
use Baleen\Cli\Exception\CliException;
use Baleen\Migrations\Version;
use Baleen\Migrations\Version\Comparator\DefaultComparator;
use BaleenTest\Baleen\Command\HandlerTestCase;
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
        $this->command = m::mock(LatestCommand::class);
        parent::setUp();
    }

    /**
     * testHandle
     * @param $versions
     * @param $lastId
     * @param callable $comparator
     * @dataProvider executeProvider
     */
    public function testHandle($versions, $lastId, callable $comparator = null)
    {
        $this->command->shouldReceive('getComparator')->once()->andReturn($comparator ?: new DefaultComparator());
        if (count($versions) > 0) {
            $migrated = $this->getMigratedCollection($versions);
            $this->output->shouldReceive('writeln')->with($lastId)->once();
        } else {
            $migrated = $versions;
            $this->output->shouldReceive('writeln')->with(m::type('string'))->once();
        }
        $this->command->shouldReceive('getStorage')->once()->andReturn($this->storage);
        $this->storage->shouldReceive('fetchAll')->once()->andReturn($migrated);
        $this->handle();
    }

    /**
     * testHandleWithInvalidComparator
     */
    public function testHandleWithInvalidComparator()
    {
        $this->command->shouldReceive('getComparator')->once()->andReturn('notCallable');
        $this->command->shouldNotReceive('getStorage');
        $this->setExpectedException(CliException::class, 'comparator');
        $this->handle();
    }
    /**
     * @return array
     */
    public function executeProvider()
    {
        return [
            [ [], 5, new DefaultComparator()],
            [ Version::fromArray(1, 2, 3, 4, 5),      5],  // simple
            [ Version::fromArray(1, 2, 3, 4, 5, -6), -6],  // last item is -6
            [ Version::fromArray(3, 5, 1, 6, 7, 2),   7],  // default order
            [ Version::fromArray(3, 5, 1, 6, 7, 2),   1, function(Version $v1, Version $v2) { // reverse order
                return (int) $v2->getId() - (int) $v1->getId();
            }],
        ];
    }
}
