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

use Baleen\Cli\CommandBus\Repository\ListMessage;
use Baleen\Cli\CommandBus\Repository\ListHandler;
use Baleen\Cli\Helper\VersionFormatter;
use Baleen\Cli\Helper\VersionFormatterInterface;
use Baleen\Migrations\Migration\MigrationInterface;
use Baleen\Migrations\Repository\RepositoryInterface;
use Baleen\Migrations\Version;
use Baleen\Migrations\Version\Collection\Linked;
use BaleenTest\Cli\CommandBus\HandlerTestCase;
use Mockery as m;

/**
 * Class ListHandlerTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class ListHandlerTest extends HandlerTestCase
{
    /**
     * setUp
     */
    public function setUp()
    {
        $this->instance = m::mock(ListHandler::class)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();
        $this->command = m::mock(ListMessage::class);
        parent::setUp();
    }

    /**
     * testHandle
     * @param Linked $versions
     * @param $newestFirst
     * @dataProvider versionsProvider
     */
    public function testHandle(Linked $versions, $newestFirst)
    {
        $this->input->shouldReceive('getOption')->with('newest-first')->once()->andReturn($newestFirst);
        $this->command->shouldReceive('getRepository->fetchAll')->once()->andReturn($versions);

        $output = m::type('string');
        if (count($versions)) {
            $output = 'v1'; // the formatted output (can be anything)
            $firstVersion = $newestFirst ? $versions->getReverse()->current() : $versions->current();
            $formatter = m::mock(VersionFormatterInterface::class);
            $this->command
                ->shouldReceive('getCliCommand->getHelper')
                ->once()
                ->with('versionFormatter')
                ->andReturn($formatter);
            $formatter->shouldReceive('formatCollection')
                ->once()
                ->with(m::on(
                    function($versions) use ($firstVersion) {
                        if (!$versions instanceof Linked) {
                            return false;
                        }
                        return $firstVersion === $versions->first();
                    }
                ))
                ->andReturn($output);
        }
        $this->output->shouldReceive('writeln')->once()->with($output);

        $this->handle();
    }

    /**
     * versionsProvider
     * @return array
     */
    public function versionsProvider()
    {
        $trueFalse = [true, false];
        $arrayCollections = [
            [],
            Version::fromArray(1, 2, 3, 4, 5),
            Version::fromArray(1, 2, 'abc', 4, 5)
        ];
        $collections = [];
        foreach ($arrayCollections as $collection) {
            foreach ($collection as $version) {
                /** @var m\Mock|Version $version */
                /** @var m\Mock|MigrationInterface $migration */
                $migration = m::mock(MigrationInterface::class);
                $version->setMigration($migration);
            }
            $collections[] = new Linked($collection);
        }
        return $this->combinations([$collections, $trueFalse]);;
    }
}
