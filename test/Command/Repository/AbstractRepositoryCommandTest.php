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

namespace BaleenTest\Baleen\Command\Repository;

use Baleen\Cli\Command\AbstractCommand;
use Baleen\Cli\Command\Repository\AbstractRepositoryCommand;
use Baleen\Cli\Command\Util\ComparatorAwareInterface;
use Baleen\Cli\Command\Util\RepositoryAwareInterface;
use Baleen\Migrations\Repository\RepositoryInterface;
use Baleen\Migrations\Version;
use BaleenTest\Baleen\BaseTestCase;
use League\Flysystem\Filesystem;
use Mockery as m;

/**
 * Class AbstractRepositoryCommandTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class AbstractRepositoryCommandTest extends BaseTestCase
{
    /** @var m\Mock|AbstractRepositoryCommand */
    protected $instance;

    /**
     * setUp
     */
    public function setUp()
    {
        parent::setUp();
        $this->instance = m::mock(AbstractRepositoryCommand::class)
            ->makePartial();
    }

    /**
     * testConstructor
     */
    public function testConstructor()
    {
        $this->assertInstanceOf(AbstractCommand::class, $this->instance);
        $this->assertInstanceOf(RepositoryAwareInterface::class, $this->instance);
        $this->assertInstanceOf(ComparatorAwareInterface::class, $this->instance);
    }

    /**
     * testGetSetRepository
     */
    public function testGetSetRepository()
    {
        /** @var m\Mock|RepositoryInterface $repository */
        $repository = m::mock(RepositoryInterface::class);
        $this->instance->setRepository($repository);
        $this->assertSame($repository, $this->instance->getRepository());
    }

    /**
     * testGetSetFilesystem
     */
    public function testGetSetFilesystem()
    {
        /** @var m\Mock|Filesystem $filesystem */
        $filesystem = m::mock(Filesystem::class);
        $this->instance->setFilesystem($filesystem);
        $this->assertSame($filesystem, $this->instance->getFilesystem());
    }
}
