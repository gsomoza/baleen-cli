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

namespace BaleenTest\Baleen\Container\ServiceProvider;

use Baleen\Cli\Command\InitCommand;
use Baleen\Cli\Command\Repository\CreateCommand;
use Baleen\Cli\Command\Storage\LatestCommand as StorageLatest;
use Baleen\Cli\Command\Repository\ListCommand as RepositoryList;
use Baleen\Cli\Command\Repository\LatestCommand as RepositoryLatest;
use Baleen\Cli\Container\ServiceProvider\CommandsProvider;
use Mockery as m;

/**
 * Class CommandsProviderTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class CommandsProviderTest extends ServiceProviderTestCase
{

    public function testRegister()
    {
        $this->setInstance(m::mock(CommandsProvider::class)->makePartial());
        $container = $this->getContainer();

        $defaultCommands = [
            StorageLatest::class,
            InitCommand::class,
            RepositoryLatest::class,
            RepositoryList::class,
            CreateCommand::class,
        ];
        foreach ($defaultCommands as $command) {
            $container->shouldReceive('add')->with($command)->once();
            $container->shouldReceive('get')->with($command)->once();
        }
        $container->shouldReceive('add')
            ->with(CommandsProvider::SERVICE_COMMANDS, m::type('callable'))
            ->once()
            ->andReturnUsing($this->assertableCallback(function(callable $factory) {
                $result = $factory();
                $this->assertInternalType('array', $result);
            }));

        $this->getInstance()->register();
    }
}
