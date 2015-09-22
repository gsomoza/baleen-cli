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

namespace BaleenTest\Cli\Container\ServiceProvider;

use Baleen\Cli\CommandBus\AbstractMessage;
use Baleen\Cli\CommandBus\Config\InitMessage;
use Baleen\Cli\CommandBus\Factory\MessageFactoryInterface;
use Baleen\Cli\Container\ServiceProvider\CommandsProvider;
use Baleen\Cli\Container\Services;
use League\Container\Container;
use League\Tactician\CommandBus;
use Mockery as m;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CommandsProviderTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class CommandsProviderTest extends ServiceProviderTestCase
{
    /**
     * testRegister
     */
    public function testRegister()
    {
        $this->setInstance(m::mock(CommandsProvider::class)->makePartial());
        $container = $this->getContainer();

        $defaultCommands = [
            Services::CMD_CONFIG_INIT,
            Services::CMD_REPOSITORY_CREATE,
            Services::CMD_REPOSITORY_LATEST,
            Services::CMD_REPOSITORY_LIST,
            Services::CMD_STORAGE_LATEST,
            Services::CMD_TIMELINE_EXECUTE,
            Services::CMD_TIMELINE_MIGRATE,
        ];

        foreach ($defaultCommands as $command) {
            $config = [
                'message' => InitMessage::class, // we only need to test a single command gets instantiated successfully
            ];
            $this->assertServiceProvided(
                $command,
                'add',
                $this->assertCallbackInstanceOf(AbstractMessage::class, [new Container(), $config])
            )->shouldReceive('withArguments')->with(m::type('array'))->once();
        }

        $container->shouldReceive('add')
            ->with(Services::COMMANDS, m::type('callable'))
            ->once()
            ->andReturnUsing($this->assertableCallback(function(callable $factory) {
                $container = new Container();
                $container->add(Services::COMMAND_BUS, new CommandBus([]));
                $result = $factory($container);
                $this->assertInternalType('array', $result);
                $withArgMock = m::mock();
                $withArgMock->shouldReceive('withArgument')->once()->with('League\Container\ContainerInterface');
                return $withArgMock;
            }));

        $container->shouldReceive('singleton')
            ->with(Services::COMMAND_BUS, m::type('callable'))
            ->once()
            ->andReturnUsing($this->assertableCallback(function(callable $factory) {
                $result = $factory();
                $this->assertInstanceOf(CommandBus::class, $result);
            }));

        $this->getInstance()->register();
    }
}
