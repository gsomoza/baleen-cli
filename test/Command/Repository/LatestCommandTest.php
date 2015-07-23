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

use Baleen\Cli\Command\Repository\LatestCommand;
use Baleen\Cli\Command\Repository\RepositoryCommand;
use Baleen\Migrations\Migration\MigrationInterface;
use Baleen\Migrations\Version as V;
use Baleen\Migrations\Version\Collection\LinkedVersions;
use BaleenTest\Baleen\Command\CommandTestCase;
use Mockery as m;

/**
 * Class LatestCommandTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class LatestCommandTest extends CommandTestCase
{
    /**
     * @inheritDoc
     */
    public function setUp()
    {
        parent::setUp();
        $this->instance = m::mock(LatestCommand::class)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();
    }

    /**
     * testConstructor
     */
    public function testConstructor()
    {
        $instance = new LatestCommand();
        $this->assertInstanceOf(RepositoryCommand::class, $instance);
        $this->assertSame(LatestCommand::COMMAND_NAME, $instance->getName());
        $this->assertNotEmpty($instance->getDescription());
    }

    /**
     * testExecute
     */
    public function testExecute()
    {
        $version = new V(1);
        $version->setMigration(m::mock(MigrationInterface::class));
        $versions = new LinkedVersions([$version]); // only thing that matters is count > 0
        $this->instance->shouldReceive('getCollection')->once()->andReturn($versions);
        $this->instance->shouldReceive('outputVersions')->once()->with($versions, $this->output);

        $this->execute();
    }

    /**
     * testExecuteNoVersions
     */
    public function testExecuteNoVersions()
    {
        // only thing that matters is that its empty
        $this->instance->shouldReceive('getCollection')->once()->andReturn([]);
        $this->instance->shouldNotReceive('outputVersions');
        $this->output->shouldReceive('writeln')->once(); // some error message
        $this->execute();
    }

    /**
     * testOutputVersions
     */
    public function testOutputVersions()
    {
        $lastId = '1'; // will always be a string
        $versions = m::mock(LinkedVersions::class);
        $versions->shouldReceive('last->getId')->once()->andReturn($lastId);
        $this->output->shouldReceive('writeln')->with($lastId)->once();
        $this->instance->outputVersions($versions, $this->output);
    }
}
