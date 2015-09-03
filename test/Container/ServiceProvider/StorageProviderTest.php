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

namespace Baleen\Cli\Container\ServiceProvider;

use Baleen\Baleen\Container\ServiceProvider\StorageProviderTest;

/**
 * Override PHP's native "touch" function under the target namespace.
 * @return mixed
 */
function touch() {
    $result = StorageProviderTest::$touchResult;
    if (null === $result) {
        $result = call_user_func_array('touch', func_get_args());
    }
    StorageProviderTest::$touchCalled = true;
    return $result;
}

namespace Baleen\Baleen\Container\ServiceProvider;

use Baleen\Cli\Container\ServiceProvider\StorageProvider;
use Baleen\Cli\Container\Services;
use Baleen\Cli\Exception\CliException;
use Baleen\Migrations\Storage\StorageInterface;
use BaleenTest\Cli\Container\ServiceProvider\ServiceProviderTestCase;
use Mockery as m;

/**
 * Class StorageProviderTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class StorageProviderTest extends ServiceProviderTestCase
{
    public static $touchResult;
    public static $touchCalled = false;

    protected $testFile;

    public function setUp()
    {
        parent::setUp();
        $this->setInstance(m::mock(StorageProvider::class)->makePartial());

        $this->testFile = __DIR__ . DIRECTORY_SEPARATOR . 'test.txt';

        $this->config
            ->shouldReceive('getStorageFilePath')
            ->once()
            ->andReturn($this->testFile);

        $this->assertSingletonProvided(
            Services::STORAGE,
            $this->assertCallbackInstanceOf(StorageInterface::class, [$this->config])
        )->shouldReceive('withArgument')->with(Services::CONFIG);

        self::$touchCalled = false;
        self::$touchResult = null;
    }

    public function testRegister()
    {
        self::$touchResult = true;
        $this->getInstance()->register();
        $this->assertTrue(self::$touchCalled, 'expected to touch the file before writing');
    }

    /**
     * @depends testRegister
     */
    public function testRegisterFileNotWritable()
    {
        self::$touchResult = false;
        $this->config
            ->shouldReceive('getStorageFile')
            ->once()
            ->andReturn($this->testFile);

        $this->setExpectedException(CliException::class, 'write');

        $this->getInstance()->register();
    }
}
