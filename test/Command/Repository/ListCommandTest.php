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

use Baleen\Cli\Command\Repository\ListCommand;
use Baleen\Cli\Command\Repository\RepositoryCommand;
use Baleen\Migrations\Migration\MigrationInterface;
use Baleen\Migrations\Version;
use Baleen\Migrations\Version\Collection\LinkedVersions;
use BaleenTest\Baleen\Command\CommandTestCase;
use Mockery as m;

/**
 * Class ListCommandTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class ListCommandTest extends CommandTestCase
{

    public function setUp()
    {
        parent::setUp();
        $this->instance = m::mock(ListCommand::class)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();
    }

    public function testConstructor()
    {
        $instance = new ListCommand();
        $this->assertInstanceOf(RepositoryCommand::class, $instance);
        $this->assertEquals(ListCommand::COMMAND_NAME, $instance->getName());
        $this->assertTrue(
            $instance->getDefinition()->hasOption('newest-first'),
            sprintf('Expected instance of "%s" to have an option called "newest-first"', RepositoryCommand::class)
        );
        $this->assertNotEmpty($instance->getDescription());
    }

    /**
     * @param LinkedVersions $versions
     * @dataProvider versionsProvider
     */
    public function testOutputVersions(LinkedVersions $versions)
    {
        foreach ($versions as $version) {
            $id = $version->getId();
            $class = str_replace('\\', '\\\\', get_class($version->getMigration()));
            $this->output->shouldReceive('writeln')->with("/$id.*$class/")->once();
        }
        $this->instance->outputVersions($versions, $this->output);
    }

    /**
     * @param LinkedVersions $versions
     * @dataProvider versionsProvider
     */
    public function testExecute(LinkedVersions $versions, $newestFirst)
    {
        $this->input->shouldReceive('getOption')->with('newest-first')->once()->andReturn($newestFirst);
        $this->instance->shouldReceive('getCollection')->once()->andReturn($versions);

        if (count($versions)) {
            //$firstVersion = $newestFirst ? $versions->getReverse()->current() : $versions->current();
            $this->instance
                ->shouldReceive('outputVersions')
                // TODO: the following doesn't work for some reason
                /*->with(m::on(function($versions) use ($firstVersion) {
                    if (!$versions instanceof LinkedVersions) {
                        return false;
                    }
                    $versions->rewind();
                    return $firstVersion === $versions->current();
                }))*/
                ->once();
        } else {
            $this->output->shouldReceive('writeln')->once();
        }

        $this->execute();
    }

    public function versionsProvider()
    {
        $cases = [
            [[], true],
            [[], false],
            [Version::fromArray(1, 2, 3, 4, 5), true],
            [Version::fromArray(1, 2, 3, 4, 5), false],
            [Version::fromArray(1, 2, 'abc', 4, 5), true],
            [Version::fromArray(1, 2, 'abc', 4, 5), false],
        ];
        $results = [];
        foreach ($cases as $case) {
            foreach ($case[0] as $version) {
                /** @var m\Mock|Version $version */
                $version->setMigration(m::mock(MigrationInterface::class));
            }
            $results[] = [new LinkedVersions($case[0]), $case[1]];
        }
        return $results;
    }
}
