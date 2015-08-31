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

namespace BaleenTest\Baleen\Command\Repository;

use Baleen\Cli\Command\Repository\AbstractRepositoryListHandler;
use Baleen\Migrations\Repository\RepositoryInterface;
use Baleen\Migrations\Version;
use Baleen\Migrations\Version\Collection\LinkedVersions;
use BaleenTest\Baleen\BaseTestCase;
use Mockery as m;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AbstractRepositoryListHandlerTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class AbstractRepositoryListHandlerTest extends BaseTestCase
{
    /** @var m\Mock|AbstractRepositoryListHandler */
    protected $instance;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->instance = m::mock(AbstractRepositoryListHandler::class)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();
    }

    /**
     * @param callable|null $comparator
     * @dataProvider comparatorProvider
     */
    public function testGetCollectionWithNoVersions(callable $comparator = null)
    {
        $repository = m::mock(RepositoryInterface::class);
        $versions = m::mock(LinkedVersions::class);
        $repository->shouldReceive('fetchAll')->once()->andReturn($versions);

        if ($comparator) {
            $versions->shouldReceive('sortWith')->with($comparator)->once();
        }

        $result = $this->invokeMethod('getCollection', $this->instance, [$repository, $comparator]);
        $this->assertSame($versions, $result);
    }

    /**
     * @return array
     */
    public function comparatorProvider()
    {
        return [
            [null],
            [function(Version $v1, Version $v2) {
                return $v1->getId() - $v2->getId();
            }] // implementation doesn't really matter
        ];
    }

    /**
     * testOutputVersions
     */
    public function testOutputVersions()
    {
        $lastVersionId = 123;
        $versions = m::mock(LinkedVersions::class);
        $versions->shouldReceive('last->getId')->once()->andReturn($lastVersionId);

        $output = m::mock(OutputInterface::class);
        $output->shouldReceive('writeln')->once()->with($lastVersionId);

        $this->invokeMethod('outputVersions', $this->instance, [$versions, $output]);
    }
}
