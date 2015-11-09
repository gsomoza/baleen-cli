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

namespace BaleenTest\Cli\CommandBus\Repository;

use Baleen\Cli\CommandBus\Repository\LatestMessage;
use Baleen\Cli\CommandBus\Repository\LatestHandler;
use Baleen\Cli\Helper\VersionFormatter;
use Baleen\Cli\Helper\VersionFormatterInterface;
use Baleen\Migrations\Migration\MigrationInterface;
use Baleen\Migrations\Repository\RepositoryInterface;
use Baleen\Migrations\Version as V;
use Baleen\Migrations\Version;
use Baleen\Migrations\Version\Collection\Linked;
use BaleenTest\Cli\CommandBus\HandlerTestCase;
use Mockery as m;

/**
 * Class LatestHandlerTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class LatestHandlerTest extends HandlerTestCase
{
    /**
     * @inheritDoc
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
     */
    public function testHandle()
    {
        $version = new V(1);
        /** @var m\Mock|MigrationInterface $migration */
        $migration = m::mock(MigrationInterface::class);
        $version->setMigration($migration);
        $versions = new Linked([$version]); // only thing that matters is count > 0

        $line = 'v1'; // could be anything
        /** @var VersionFormatterInterface|m\Mock $formatter */
        $formatter = m::mock(VersionFormatterInterface::class);
        $formatter->shouldReceive('formatVersion')->once()->with($version)->andReturn($line);

        $this->command
            ->shouldReceive('getRepository->fetchAll')
            ->once()
            ->andReturn($versions);
        $this->command->shouldReceive('getCliCommand->getHelper')->with('versionFormatter')->andReturn($formatter);
        $this->output->shouldReceive('writeln')->with($line)->once();

        $this->handle();
    }

    /**
     * testHandleNoVersions
     */
    public function testHandleNoVersions()
    {
        // only thing that matters is that its empty
        $this->command->shouldReceive('getRepository->fetchAll')->once()->andReturn([]);
        $this->instance->shouldNotReceive('outputVersions');
        $this->output->shouldReceive('writeln')->once(); // some error message
        $this->handle();
    }
}
