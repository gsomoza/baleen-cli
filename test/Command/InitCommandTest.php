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

namespace Baleen\Cli\Command;

use BaleenTest\Baleen\Command\InitCommandTest;

/**
 * Mock for PHP's file_exists
 */
function file_exists() {
    $result = InitCommandTest::$fileExistsResult;
    if (null === $result) {
        $result = call_user_func_array('file_exists', func_get_args());
    }
    return $result;
}

namespace BaleenTest\Baleen\Command;

use Baleen\Cli\Command\AbstractCommand;
use Baleen\Cli\Command\InitCommand;
use Baleen\Cli\Config\AppConfig;
use Mockery as m;

/**
 * Class InitCommandTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class InitCommandTest extends CommandTestCase
{

    public static $fileExistsResult = null;

    /**
     * setUp
     */
    public function setUp()
    {
        parent::setUp();
        $this->instance = m::mock(InitCommand::class)->makePartial();
    }

    public function tearDown()
    {
        parent::tearDown();
        self::$fileExistsResult = null;
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
        // not important for this test
        $confFile = __DIR__ . DIRECTORY_SEPARATOR . 'config.yml';

        /** @var m\Mock|AppConfig $config */
        $config = m::mock(AppConfig::class);
        $config->shouldReceive('getConfigFilePath')->andReturn($confFile);
        $config->shouldReceive('getConfigFileName')->andReturn(pathinfo($confFile, PATHINFO_FILENAME));
        $config->shouldReceive('write')->once()->andReturn($writeResult);

        $resultMessage = $writeResult ? 'created at' : 'Could not create';
        $this->output->shouldReceive('writeln')->with(m::on(function($message) use ($resultMessage) {
            return strpos($message, $resultMessage) !== false;
        }))->once();

        $this->instance->setConfig($config);

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
        $config->shouldReceive('getConfigFilePath');
        $config->shouldReceive('getConfigFileName');
        $this->instance->setConfig($config);

        $this->output->shouldReceive('writeln')->with('/already initiali[zs]ed/')->once();
        $config->shouldNotReceive('write');

        self::$fileExistsResult = true;

        $this->execute();
    }
}
