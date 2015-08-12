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
use Baleen\Cli\Container\ServiceProvider\AppConfigProvider;
use Baleen\Cli\Container\ServiceProvider\CommandsProvider;
use Baleen\Cli\Container\ServiceProvider\DefaultProvider;
use Baleen\Cli\Container\ServiceProvider\HelperSetProvider;
use Baleen\Cli\Container\ServiceProvider\RepositoryProvider;
use Baleen\Cli\Container\ServiceProvider\StorageProvider;
use Baleen\Migrations\Version\Comparator\DefaultComparator;
use League\Container\Definition\ClassDefinition;
use Mockery as m;

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
                'setComparator' => [DefaultComparator::class],
                'setConfig' => [AppConfigProvider::SERVICE_CONFIG],
            ],
            AbstractRepositoryCommand::class => [
                'setRepository' => [RepositoryProvider::SERVICE_REPOSITORY],
                'setFilesystem' => [RepositoryProvider::SERVICE_FILESYSTEM],
            ],
            AbstractStorageCommand::class => [
                'setStorage' => [StorageProvider::SERVICE_STORAGE],
            ],
            InitCommand::class => [
                'setConfigStorage' => [AppConfigProvider::SERVICE_CONFIG_STORAGE],
            ],
        ];
        foreach ($inflectors as $name => $withMethods) {
            $this->assertRegistersInflector($name, $withMethods);
        }

        $classDefinitionMock = m::mock(ClassDefinition::class);
        $classDefinitionMock->shouldReceive('withArguments')->with(m::type('array'))->once();
        $this->getContainer()
            ->shouldReceive('singleton')
            ->with(Application::class)
            ->once()
            ->andReturn($classDefinitionMock);

        $this->getContainer()->shouldReceive('singleton')->with(DefaultComparator::class)->once();

        $this->getInstance()->register();
    }

    /**
     * testIsRegistered
     */
    public function testIsRegistered()
    {
        $this->getContainer()->shouldReceive('isRegistered')->with(Application::class)->once()->andReturn(true);
        $this->getContainer()->shouldNotReceive('addServiceProvider', 'inflector', 'singleton', 'add');
        $this->getInstance()->register();
    }
}
