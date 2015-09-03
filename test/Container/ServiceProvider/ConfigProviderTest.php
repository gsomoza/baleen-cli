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

use Baleen\Cli\Config\Config;
use Baleen\Cli\Config\ConfigStorage;
use Baleen\Cli\Container\ServiceProvider\ConfigProvider;
use Baleen\Cli\Container\Services;
use Mockery as m;

/**
 * Class ConfigProviderTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class ConfigProviderTest extends ServiceProviderTestCase
{
    /**
     * testRegister
     */
    public function testRegister()
    {
        $configStorage = m::mock(ConfigStorage::class);
        $appConfigMock = m::mock(Config::class);
        $configStorage->shouldReceive('load')->with(m::type('string'))->once()->andReturn($appConfigMock);

        $this->setInstance(m::mock(ConfigProvider::class)->makePartial());

        $localConfigFolder = realpath(implode(DIRECTORY_SEPARATOR, [__DIR__, '..', '..', '..']));
        $this->assertFileExists($localConfigFolder);

        $this->getInstance()->getContainer()
            ->shouldReceive('get')
            ->with(Services::BALEEN_BASE_DIR)
            ->once()
            ->andReturn($localConfigFolder);

        $this->assertSingletonProvided(
            Services::CONFIG,
            $this->assertCallbackInstanceOf(Config::class, $configStorage)
        )->shouldReceive('withArgument')->with(Services::CONFIG_STORAGE);

        $this->assertSingletonProvided(
            Services::CONFIG_STORAGE,
            $this->assertCallbackInstanceOf(ConfigStorage::class)
        );

        $this->instance->register();
    }
}
