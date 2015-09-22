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

use Baleen\Cli\Application;
use Baleen\Cli\CommandBus\AbstractMessage;
use Baleen\Cli\CommandBus\Util\ComparatorAwareInterface;
use Baleen\Cli\CommandBus\Util\ConfigStorageAwareInterface;
use Baleen\Cli\CommandBus\Util\RepositoryAwareInterface;
use Baleen\Cli\CommandBus\Util\StorageAwareInterface;
use Baleen\Cli\CommandBus\Util\TimelineAwareInterface;
use Baleen\Cli\Container\ServiceProvider\ApplicationProvider;
use Baleen\Cli\Container\Services;
use Mockery as m;
use Symfony\Component\Console\Helper\HelperSet;

/**
 * Class ApplicationProviderTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class ApplicationProviderTest extends ServiceProviderTestCase
{
    /**
     * setUp
     */
    public function setUp()
    {
        parent::setUp();
        $this->setInstance(m::mock(ApplicationProvider::class)->makePartial());
    }

    /**
     * testRegister
     * @dataProvider trueFalseProvider
     * @param $isAppRegistered
     */
    public function testRegister($isAppRegistered)
    {
        $inflectors = [
            RepositoryAwareInterface::class => [
                'setRepository' => [Services::REPOSITORY],
                'setFilesystem' => [Services::REPOSITORY_FILESYSTEM],
            ],
            StorageAwareInterface::class => [
                'setStorage' => [Services::STORAGE],
            ],
            TimelineAwareInterface::class => [
                'setTimeline' => [Services::TIMELINE],
            ],
            ComparatorAwareInterface::class => [
                'setComparator' => [Services::TIMELINE_COMPARATOR],
            ],
            ConfigStorageAwareInterface::class => [
                'setConfigStorage' => [Services::CONFIG_STORAGE],
            ],
            AbstractMessage::class => [
                'setConfig' => [Services::CONFIG],
            ]
        ];
        foreach ($inflectors as $name => $withMethods) {
            $this->assertRegistersInflector($name, $withMethods);
        }

        $this->container->shouldReceive('isRegistered')
            ->with(Services::APPLICATION)
            ->once()
            ->andReturn($isAppRegistered);

        if (!$isAppRegistered) {
            $this->assertSingletonProvided(
                Services::APPLICATION,
                $this->assertCallbackInstanceOf( Application::class, [[], new HelperSet()]),
                'string'
            )->shouldReceive('withArguments')->with([Services::COMMANDS, Services::HELPERSET])->once();
        }


        $this->getInstance()->register();
    }
}
