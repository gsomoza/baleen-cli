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

/**
 * Override mkdir() in target namespace for testing
 *
 * @return int
 */
namespace Baleen\Cli\Container\ServiceProvider;

use BaleenTest\Baleen\Container\ServiceProvider\RepositoryProviderTest;

function mkdir()
{
    $mkDirResult = RepositoryProviderTest::$mkDirResult;
    if (null === $mkDirResult) { // means we didn't want to mock it, so call the real function
        $mkDirResult = call_user_func_array('mkdir', func_get_args());
    }
    return $mkDirResult;
}

namespace BaleenTest\Baleen\Container\ServiceProvider;

use Baleen\Cli\Config\AppConfig;
use Baleen\Cli\Container\ServiceProvider\AppConfigProvider;
use Baleen\Cli\Container\ServiceProvider\DefaultProvider;
use Baleen\Cli\Container\ServiceProvider\RepositoryProvider;
use Baleen\Cli\Exception\CliException;
use Baleen\Migrations\Repository\DirectoryRepository;
use Composer\Autoload\ClassLoader;
use Mockery as m;

/**
 * Class RepositoryProviderTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class RepositoryProviderTest extends ServiceProviderTestCase
{

    /** @var m\Mock */
    protected $config;

    protected $autoloader;

    /** @var boolean Used to mock PHP's "mkdir" function */
    public static $mkDirResult;

    public function setUp()
    {
        parent::setUp();

        $config = m::mock(AppConfig::class);
        $this->config = $config;

        $autoloaderMock = m::mock(ClassLoader::class);
        $autoloaderMock->shouldReceive('addPsr4')->with(__NAMESPACE__ . '\\', __DIR__);
        $this->autoloader = $autoloaderMock;

        $this->setInstance(m::mock(RepositoryProvider::class)->makePartial());

        $this->getContainer()
            ->shouldReceive('get')
            ->zeroOrMoreTimes()
            ->with(DefaultProvider::SERVICE_AUTOLOADER)
            ->andReturn($this->autoloader);
    }

    public function tearDown()
    {
        $this->config = null;
        $this->autoloader = null;
        $this->instance = null;
        $this->container = null;
        parent::tearDown();
    }

    public function testRegister()
    {
        $this->config->shouldReceive('getMigrationsNamespace')->once()->andReturn(__NAMESPACE__);
        $this->config->shouldReceive('getMigrationsDirectoryPath')->once()->andReturn(__DIR__);

        $this->assertSingletonProvided(
            RepositoryProvider::SERVICE_REPOSITORY,
            $this->assertCallbackInstanceOf(DirectoryRepository::class, [$this->config])
        )->shouldReceive('withArgument')->with(AppConfigProvider::SERVICE_CONFIG);

        $this->getInstance()->register();
    }

    public function testFactoryCreatesDirectory()
    {
        $newDir = __DIR__ . '/newdir';
        $this->assertFalse(file_exists($newDir), sprintf('expected directory "%s" to not exist', $newDir));

        $this->config->shouldReceive('getMigrationsNamespace')->once()->andReturn(__NAMESPACE__);
        $this->config->shouldReceive('getMigrationsDirectoryPath')->once()->andReturn($newDir);

        $this->assertSingletonProvided(
            RepositoryProvider::SERVICE_REPOSITORY,
            $this->assertCallbackInstanceOf(DirectoryRepository::class, [$this->config])
        )->shouldReceive('withArgument')->with(AppConfigProvider::SERVICE_CONFIG);

        try {
            $this->getInstance()->register();
            $this->assertTrue(file_exists($newDir), sprintf('expected directory "%s" to have been created', $newDir));
        } catch (\Exception $e) {
            // nothing
        } finally {
            rmdir($newDir);
        }
    }

    public function testFactoryFailToCreateDirectory()
    {
        $newDir = __DIR__ . '/newdir';
        $this->assertFalse(file_exists($newDir), sprintf('expected directory "%s" to not exist', $newDir));

        $this->config->shouldNotReceive('getMigrationsNamespace');
        $this->config->shouldReceive('getMigrationsDirectoryPath')->once()->andReturn($newDir);

        $this->assertSingletonProvided(
            RepositoryProvider::SERVICE_REPOSITORY,
            $this->assertCallbackInstanceOf(DirectoryRepository::class, [$this->config])
        )->shouldReceive('withArgument')->with(AppConfigProvider::SERVICE_CONFIG);

        self::$mkDirResult = false;

        $this->setExpectedException(CliException::class);
        $this->getInstance()->register();
    }
}
