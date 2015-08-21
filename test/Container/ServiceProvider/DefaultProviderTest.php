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

use Baleen\Cli\Application;
use Baleen\Cli\Command\AbstractCommand;
use Baleen\Cli\Command\InitCommand;
use Baleen\Cli\Command\Repository\AbstractRepositoryCommand;
use Baleen\Cli\Command\Storage\AbstractStorageCommand;
use Baleen\Cli\Container\ServiceProvider\DefaultProvider;
use Baleen\Cli\Container\Services;
use Mockery as m;
use Symfony\Component\Console\Helper\HelperSet;

/**
 * Class DefaultProviderTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class DefaultProviderTest extends ServiceProviderTestCase
{

    /**
     * setUp
     */
    public function setUp()
    {
        parent::setUp();
        $this->setInstance(m::mock(DefaultProvider::class)->makePartial());
    }

    /**
     * testRegister
     */
    public function testRegister()
    {
        $inflectors = [
            AbstractCommand::class => [
                'setComparator' => [Services::TIMELINE_COMPARATOR],
                'setConfig' => [Services::CONFIG],
            ],
            AbstractRepositoryCommand::class => [
                'setRepository' => [Services::REPOSITORY],
                'setFilesystem' => [Services::REPOSITORY_FILESYSTEM],
            ],
            AbstractStorageCommand::class => [
                'setStorage' => [Services::STORAGE],
            ],
            InitCommand::class => [
                'setConfigStorage' => [Services::CONFIG_STORAGE],
            ],
        ];
        foreach ($inflectors as $name => $withMethods) {
            $this->assertRegistersInflector($name, $withMethods);
        }

        $this->assertSingletonProvided(
            Services::APPLICATION,
            $this->assertCallbackInstanceOf( Application::class, [[], new HelperSet()]),
            'string'
        )->shouldReceive('withArguments')->with([Services::COMMANDS, Services::HELPERSET])->once();

        $this->getInstance()->register();
    }

    /**
     * testIsRegistered
     */
    public function testIsRegistered()
    {
        $container = $this->getContainer();
        $container->shouldReceive('isRegistered')->with(Services::APPLICATION)->once()->andReturn(true);
        $container->shouldNotReceive('singleton');
        $inflectorMock = m::mock();
        $inflectorMock->shouldReceive('invokeMethod')->atLeast(1)->andReturnSelf();
        $container->shouldReceive('inflector')->atLeast(1)->andReturn($inflectorMock);
        $this->getInstance()->register();
    }
}
