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

use Baleen\Cli\Config\AppConfig;
use Baleen\Cli\Config\ConfigStorage;
use Baleen\Cli\Container\ServiceProvider\AppConfigProvider;
use Mockery as m;

/**
 * Class AppConfigProviderTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class AppConfigProviderTest extends ServiceProviderTestCase
{
    /**
     * testRegister
     */
    public function testRegister()
    {
        $configStorage = m::mock(ConfigStorage::class);
        $appConfigMock = m::mock(AppConfig::class);
        $configStorage->shouldReceive('load')->with(m::type('string'))->once()->andReturn($appConfigMock);

        $this->setInstance(m::mock(AppConfigProvider::class)->makePartial());

        $localConfigFolder = realpath(implode(DIRECTORY_SEPARATOR, [__DIR__, '..', '..', '..']));
        $this->assertFileExists($localConfigFolder);

        $this->getInstance()->getContainer()
            ->shouldReceive('get')
            ->with(AppConfigProvider::BALEEN_BASE_DIR)
            ->once()
            ->andReturn($localConfigFolder);

        $this->assertSingletonProvided(
            AppConfigProvider::SERVICE_CONFIG,
            $this->assertCallbackInstanceOf(AppConfig::class, $configStorage)
        )->shouldReceive('withArgument')->with(AppConfigProvider::SERVICE_CONFIG_STORAGE);

        $this->assertSingletonProvided(
            AppConfigProvider::SERVICE_CONFIG_STORAGE,
            $this->assertCallbackInstanceOf(ConfigStorage::class)
        );

        $this->instance->register();
    }
}
