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
namespace Baleen\Cli\Provider;

use BaleenTest\Cli\Provider\RepositoryProviderTest;

function mkdir()
{
    $mkDirResult = RepositoryProviderTest::$mkDirResult;
    if (null === $mkDirResult) { // means we didn't want to mock it, so call the real function
        $mkDirResult = call_user_func_array('mkdir', func_get_args());
    }
    return $mkDirResult;
}

namespace BaleenTest\Cli\Provider;

use Baleen\Cli\Exception\CliException;
use Baleen\Cli\Provider\Services;
use Baleen\Migrations\Migration\Factory\SimpleFactory;
use Baleen\Migrations\Repository\DirectoryRepository;
use Composer\Autoload\ClassLoader;
use Mockery as m;

/**
 * Class RepositoryProviderTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class RepositoryProviderTest extends ServiceProviderTestCase
{
    /** @var ClassLoader */
    protected $autoloader;

    /** @var boolean Used to mock PHP's "mkdir" function */
    public static $mkDirResult;

    /**
     * setUp
     */
    public function setUp()
    {
        parent::setUp();

        $autoloaderMock = m::mock(ClassLoader::class);
        $autoloaderMock->shouldReceive('addPsr4')->with(__NAMESPACE__ . '\\', __DIR__);
        $this->autoloader = $autoloaderMock;

        $this->setInstance(m::mock(\Baleen\Cli\Provider\RepositoryProvider::class)->makePartial());

        $this->getContainer()
            ->shouldReceive('get')
            ->zeroOrMoreTimes()
            ->with(Services::AUTOLOADER)
            ->andReturn($this->autoloader);
    }

    /**
     * tearDown
     */
    public function tearDown()
    {
        $this->config = null;
        $this->autoloader = null;
        $this->instance = null;
        $this->container = null;
        parent::tearDown();
    }

    /**
     * testRegister
     */
    public function testRegister()
    {
        $this->config->shouldReceive('getMigrationsNamespace')->once()->andReturn(__NAMESPACE__);
        $this->config->shouldReceive('getDefaultMigrationsDirectoryPath')->once()->andReturn(__DIR__);

        $this->assertSingletonProvided(
            Services::MIGRATION_FACTORY,
            $this->assertCallbackInstanceOf(SimpleFactory::class),
            'string'
        );

        $this->assertSingletonProvided(
            Services::REPOSITORY,
            $this->assertCallbackInstanceOf(DirectoryRepository::class, [$this->config, new SimpleFactory()])
        )->shouldReceive('withArguments')->with([Services::CONFIG, Services::MIGRATION_FACTORY]);

        $this->getInstance()->register();
    }

    /**
     * testFactoryCreatesDirectory
     */
    public function testFactoryCreatesDirectory()
    {
        $newDir = __DIR__ . '/newdir';
        $this->assertFalse(file_exists($newDir), sprintf('expected directory "%s" to not exist', $newDir));

        $this->config->shouldReceive('getMigrationsNamespace')->once()->andReturn(__NAMESPACE__);
        $this->config->shouldReceive('getDefaultMigrationsDirectoryPath')->once()->andReturn($newDir);

        // TODO: refactor across tests
        $this->assertSingletonProvided(
            Services::MIGRATION_FACTORY,
            $this->assertCallbackInstanceOf(SimpleFactory::class),
            'string'
        );

        $this->assertSingletonProvided(
            Services::REPOSITORY,
            $this->assertCallbackInstanceOf(DirectoryRepository::class, [$this->config, new SimpleFactory()])
        )->shouldReceive('withArguments')->with([Services::CONFIG, Services::MIGRATION_FACTORY]);

        try {
            $this->getInstance()->register();
            $this->assertTrue(file_exists($newDir), sprintf('expected directory "%s" to have been created', $newDir));
        } catch (\Exception $e) {
            // nothing
        } finally {
            rmdir($newDir);
        }
    }

    /**
     * testFactoryFailToCreateDirectory
     */
    public function testFactoryFailToCreateDirectory()
    {
        $newDir = __DIR__ . '/newdir';
        $this->assertFalse(file_exists($newDir), sprintf('expected directory "%s" to not exist', $newDir));

        $this->config->shouldNotReceive('getMigrationsNamespace');
        $this->config->shouldReceive('getDefaultMigrationsDirectoryPath')->once()->andReturn($newDir);

        // TODO: refactor across tests
        $this->assertSingletonProvided(
            Services::MIGRATION_FACTORY,
            $this->assertCallbackInstanceOf(SimpleFactory::class),
            'string'
        );

        $this->assertSingletonProvided(
            Services::REPOSITORY,
            $this->assertCallbackInstanceOf(DirectoryRepository::class, [$this->config, new SimpleFactory()])
        )->shouldReceive('withArguments')->with([Services::CONFIG, Services::MIGRATION_FACTORY]);

        self::$mkDirResult = false;

        $this->setExpectedException(CliException::class);
        $this->getInstance()->register();
    }
}
