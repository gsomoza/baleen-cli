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

namespace BaleenTest\Cli\Provider;

use Baleen\Cli\Provider\Services;
use Baleen\Cli\Provider\StorageProvider;
use Baleen\Migrations\Storage\StorageInterface;
use Mockery as m;

/**
 * Class StorageProviderTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class StorageProviderTest extends ServiceProviderTestCase
{
    /**
     * setUp
     */
    public function setUp()
    {
        parent::setUp();
        $this->setInstance(m::mock(StorageProvider::class)->makePartial());
    }

    /**
     * testRegister
     */
    public function testRegister()
    {
        $this->config
            ->shouldReceive('getStorageFile')
            ->once()
            ->andReturn('/some/file/path'); // path not important for this test
        $this->assertSingletonProvided(
            Services::VERSION_REPOSITORY,
            $this->assertCallbackInstanceOf(StorageInterface::class, [$this->config])
        )->shouldReceive('withArgument')->with(Services::CONFIG);
        $this->getInstance()->register();
    }
}
