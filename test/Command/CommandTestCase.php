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

namespace BaleenTest\Baleen\Command;

use Baleen\Cli\Command\Storage\LatestCommand;
use Baleen\Migrations\Storage\StorageInterface;
use Baleen\Migrations\Version;
use Baleen\Migrations\Version\Collection\MigratedVersions;
use BaleenTest\Baleen\BaseTestCase;
use Mockery as m;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CommandTestCase
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class CommandTestCase extends BaseTestCase
{
    /** @var m\Mock|InputInterface */
    protected $input;
    /** @var m\Mock|OutputInterface */
    protected $output;
    /** @var m\Mock|LatestCommand */
    protected $instance;
    /** @var m\Mock|StorageInterface */
    protected $storage;

    /**
     * setUp
     */
    public function setUp()
    {
        parent::setUp();
        $this->input = m::mock(InputInterface::class);
        $this->output = m::mock(OutputInterface::class);
        $this->storage = m::mock(StorageInterface::class);
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
    }

    /**
     * Assers that the command is named after its overwritten COMMAND_NAME constant
     * @param Command $command
     */
    protected function assertCommandIsNamedProperly(Command $command)
    {
        $this->assertEquals(LatestCommand::COMMAND_NAME, $command->getName());
    }

    /**
     * Calls execute() on the current instance, passing the current input and output mocks
     */
    protected function execute()
    {
        $this->instance->execute($this->input, $this->output);
    }

    /**
     * @param $versions
     * @return MigratedVersions
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
        return new MigratedVersions($versions);
    }
}
