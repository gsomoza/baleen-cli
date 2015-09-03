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
use Baleen\Cli\CommandBus\MessageInterface;
use Baleen\Cli\Config\Config;
use BaleenTest\Cli\BaseTestCase;
use Mockery as m;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AbstractMessageTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class AbstractMessageTest extends BaseTestCase
{
    /** @var m\Mock|AbstractMessage */
    protected $instance;

    /**
     * setUp
     */
    public function setUp()
    {
        $this->instance = m::mock(AbstractMessage::class)->makePartial();
    }

    public function testConstructor()
    {
        $this->assertInstanceOf(MessageInterface::class, $this->instance);
    }

    /**
     * tearDown
     */
    public function tearDown()
    {
        parent::tearDown();
        $this->instance = null;
    }

    /**
     * testSetGetConfig
     */
    public function testSetGetConfig()
    {
        /** @var Config $config */
        $config = m::mock(Config::class);
        $this->instance->setConfig($config);
        $this->assertSame($config, $this->instance->getConfig());
    }

    /**
     * testGetSetInput
     */
    public function testGetSetInput()
    {
        /** @var InputInterface $input */
        $input = m::mock(InputInterface::class);
        $this->instance->setInput($input);
        $this->assertSame($input, $this->instance->getInput());
    }

    /**
     * testGetSetOutput
     */
    public function testGetSetOutput()
    {
        /** @var OutputInterface $output */
        $output = m::mock(OutputInterface::class);
        $this->instance->setOutput($output);
        $this->assertSame($output, $this->instance->getOutput());
    }

    /**
     * testGetSetCliCommand
     */
    public function testGetSetCliCommand()
    {
        /** @var Command $cliCommand */
        $cliCommand = m::mock(Command::class);
        $this->instance->setCliCommand($cliCommand);
        $this->assertSame($cliCommand, $this->instance->getCliCommand());
    }
}
