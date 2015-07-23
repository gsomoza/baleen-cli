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

use Baleen\Cli\Command\AbstractCommand;
use Baleen\Cli\Command\Repository\RepositoryCommand;
use Baleen\Migrations\Repository\RepositoryInterface;
use Baleen\Migrations\Version;
use Baleen\Migrations\Version\Collection\LinkedVersions;
use BaleenTest\Baleen\Command\CommandTestCase;
use League\Flysystem\Filesystem;
use Mockery as m;

/**
 * Class RepositoryCommandTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class RepositoryCommandTest extends CommandTestCase
{

    /**
     * setUp
     */
    public function setUp()
    {
        parent::setUp();
        $this->instance = m::mock(RepositoryCommand::class)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();
    }

    /**
     * testConstructor
     */
    public function testConstructor()
    {
        $this->assertInstanceOf(AbstractCommand::class, $this->instance);
    }

    /**
     * testGetSetRepository
     */
    public function testGetSetRepository()
    {
        $repository = m::mock(RepositoryInterface::class);
        $this->instance->setRepository($repository);
        $this->assertSame($repository, $this->instance->getRepository());
    }

    /**
     * testGetSetFilesystem
     */
    public function testGetSetFilesystem()
    {
        $filesystem = m::mock(Filesystem::class);
        $this->instance->setFilesystem($filesystem);
        $this->assertSame($filesystem, $this->instance->getFilesystem());
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
        $this->instance->setRepository($repository);

        if ($comparator) {
            $this->instance->setComparator($comparator);
            $versions->shouldReceive('sortWith')->with($comparator)->once();
        }

        $result = $this->instance->getCollection();
        $this->assertSame($this->getPropVal('versions', $this->instance), $result);
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
            }] // implementation doen't really matter
        ];
    }
}
