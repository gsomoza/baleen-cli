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

namespace BaleenTest\Cli\CommandBus\Config;

use Baleen\Cli\CommandBus\Config\InitMessage;
use Baleen\Cli\CommandBus\Config\InitHandler;
use Baleen\Cli\Config\Config;
use Baleen\Cli\Config\ConfigStorage;
use BaleenTest\Cli\CommandBus\HandlerTestCase;
use Mockery as m;

/**
 * Class InitHandlerTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class InitHandlerTest extends HandlerTestCase
{

    /** @var m\Mock|ConfigStorage */
    protected $configStorage;

    /**
     * setUp
     */
    public function setUp()
    {
        $this->instance = m::mock(InitHandler::class)->makePartial();
        $this->command = m::mock(InitMessage::class)->makePartial();
        $this->configStorage = m::mock(ConfigStorage::class);
        $this->command->shouldReceive('getConfigStorage')->zeroOrMoreTimes()->andReturn($this->configStorage);
        parent::setUp();
    }

    /**
     * @param $writeResult
     * @dataProvider executeProvider
     */
    public function testExecute($writeResult)
    {
        $configFileName = '.baleen.yml';

        /** @var m\Mock|Config $config */
        $config = m::mock(Config::class);

        $this->command->setConfig($config);

        $resultMessage = $writeResult ? 'created at' : 'Could not create';
        $this->output->shouldReceive('writeln')->with(m::on(function($message) use ($resultMessage) {
            return strpos($message, $resultMessage) !== false;
        }))->once();


        $config->shouldReceive('getFileName')->once()->andReturn($configFileName);
        $this->configStorage->shouldReceive('isInitialized')->once()->andReturn(false);
        $this->configStorage->shouldReceive('write')->once()->andReturn($writeResult);

        $this->handle();
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
        /** @var m\Mock|Config $config */
        $config = m::mock(Config::class);
        $this->command->setConfig($config);
        $this->command->shouldReceive('getCliCommand->getApplication->getName')->andReturn('Baleen');

        $this->output->shouldReceive('writeln')->with('/already initiali[zs]ed/')->once();
        $config->shouldNotReceive('write');

        $this->configStorage->shouldReceive('isInitialized')->once()->andReturn(true);

        $this->handle();
    }
}
