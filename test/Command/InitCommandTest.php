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

use Baleen\Cli\Command\AbstractCommand;
use Baleen\Cli\Command\InitCommand;
use Baleen\Cli\Config\AppConfig;
use Baleen\Cli\Config\ConfigStorage;
use Mockery as m;

/**
 * Class InitCommandTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class InitCommandTest extends CommandTestCase
{

    /** @var ConfigStorage|m\Mock */
    protected $configStorage;

    /**
     * setUp
     */
    public function setUp()
    {
        parent::setUp();
        $this->instance = m::mock(InitCommand::class)->makePartial();
        $this->configStorage = m::mock(ConfigStorage::class);
        $this->instance->setConfigStorage($this->configStorage);
    }

    /**
     * testConstructor
     */
    public function testConstructor()
    {
        $instance = new InitCommand();
        $this->assertInstanceOf(AbstractCommand::class, $instance);
        $this->assertEquals(InitCommand::COMMAND_NAME, $instance->getName());
    }

    /**
     * @param $writeResult
     * @dataProvider executeProvider
     */
    public function testExecute($writeResult)
    {
        $configFileName = '.baleen.yml';
        /** @var m\Mock|AppConfig $config */
        $config = m::mock(AppConfig::class);
        $this->instance->setConfig($config);

        $resultMessage = $writeResult ? 'created at' : 'Could not create';
        $this->output->shouldReceive('writeln')->with(m::on(function($message) use ($resultMessage) {
            return strpos($message, $resultMessage) !== false;
        }))->once();


        $this->configStorage->shouldReceive('isInitialized')->once()->andReturn(false);
        $this->configStorage->shouldReceive('getConfigFileName')->once()->andReturn($configFileName);
        $this->configStorage->shouldReceive('write')->once()->andReturn($writeResult);

        $this->execute();
    }

    /**
     * @return array
     */
    public function executeProvider()
    {
        return [
            [ true ],
            [ false ],
        ];
    }

    /**
     * testExecuteExitsEarlyIfFileExists
     */
    public function testExecuteExitsEarlyIfFileExists()
    {
        /** @var m\Mock|AppConfig $config */
        $config = m::mock(AppConfig::class);
        $this->instance->setConfig($config);

        $this->output->shouldReceive('writeln')->with('/already initiali[zs]ed/')->once();
        $config->shouldNotReceive('write');

        $this->configStorage->shouldReceive('isInitialized')->once()->andReturn(true);

        $this->execute();
    }
}
