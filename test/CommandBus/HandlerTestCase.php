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

namespace BaleenTest\Cli\CommandBus;

use Baleen\Cli\CommandBus\AbstractMessage;
use Baleen\Cli\CommandBus\Storage\LatestMessage;
use Baleen\Migrations\Storage\StorageInterface;
use Baleen\Migrations\Version;
use Baleen\Migrations\Version\Collection\Migrated;
use BaleenTest\Cli\BaseTestCase;
use Mockery as m;
use Mockery\Matcher\MatcherAbstract;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class HandlerTestCase
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class HandlerTestCase extends BaseTestCase
{
    /** @var m\Mock|InputInterface */
    protected $input;

    /** @var m\Mock|OutputInterface */
    protected $output;

    /** @var m\Mock|LatestMessage */
    protected $instance;

    /** @var m\Mock|StorageInterface */
    protected $storage;

    /** @var m\Mock|AbstractMessage */
    protected $command;

    /**
     * setUp
     */
    public function setUp()
    {
        parent::setUp();
        $this->input = m::mock(InputInterface::class);
        $this->output = m::mock(OutputInterface::class);
        $this->storage = m::mock(StorageInterface::class);
        if ($this->command) {
            $this->command->shouldReceive('getInput')->zeroOrMoreTimes()->andReturn($this->input);
            $this->command->shouldReceive('getOutput')->zeroOrMoreTimes()->andReturn($this->output);
        }
    }

    /**
     * tearDown
     */
    public function tearDown()
    {
        parent::tearDown();
        $this->instance = null;
        $this->input = null;
        $this->output = null;
        $this->storage = null;
        $this->command = null;
    }

    /**
     * Assers that the command is named after its overwritten COMMAND_NAME constant
     * @param Command $command
     */
    protected function assertCommandIsNamedProperly(Command $command)
    {
        $this->assertNotEmpty(LatestMessage::COMMAND_NAME);
        $this->assertContains(LatestMessage::COMMAND_NAME, $command->getName());
    }

    /**
     * Calls handle() on the current instance, passing the current input and output mocks
     */
    protected function handle()
    {
        $this->instance->handle($this->command);
    }

    /**
     * @param $versions
     * @return Migrated
     */
    protected function getMigratedCollection(array $versions)
    {
        if (!count($versions)) {
            return $versions;
        }
        foreach ($versions as $version) {
            /** @var Version $version */
            $version->setMigrated(true);
        }
        return new Migrated($versions);
    }

    /**
     * @param Command $instance
     * @param $name
     */
    protected function assertHasArgument(Command $instance, $name)
    {
        $this->assertTrue(
            $instance->getDefinition()->hasArgument($name),
            sprintf("Expected command to have an argument named '%s'.", $name)
        );
    }

    /**
     * @param Command $instance
     * @param $name
     */
    protected function assertHasOption(Command $instance, $name)
    {
        $this->assertTrue(
            $instance->getDefinition()->hasOption($name),
            sprintf("Expected command to have an argument named '%s'.", $name)
        );
    }

    /**
     * @param Command $instance
     * @param $alias
     */
    protected function assertHasAlias(Command $instance, $alias)
    {
        $this->assertContains($alias, $instance->getAliases());
    }

    /**
     * @param mixed $result
     * @param MatcherAbstract $validator
     */
    protected function assertQuestionAsked($result = null, MatcherAbstract $validator = null)
    {
        $helper = m::mock();
        $helper->shouldReceive('ask')->with($this->input, $this->output, m::on(function($param) use ($validator) {
            return null !== $validator ? $validator->match($param) : true;
        }))->once()->andReturn($result);
        $this->command->shouldReceive('getCliCommand->getHelper')->with('question')->andReturn($helper);
    }
}
