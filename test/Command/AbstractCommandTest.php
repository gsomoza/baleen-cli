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

namespace BaleenTest\Baleen\Command;

use Baleen\Cli\Command\AbstractCommand;
use Baleen\Cli\Config\Config;
use Mockery as m;

/**
 * Class AbstractCommandTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class AbstractCommandTest extends CommandTestCase
{

    public function setUp()
    {
        parent::setUp();
        $this->instance = m::mock(AbstractCommand::class)->makePartial();
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->instance = null;
    }

    public function testSetGetConfig()
    {
        $config = m::mock(Config::class);
        $this->instance->setConfig($config);

        $this->assertSame($config, $this->getPropVal('config', $this->instance));
    }

    public function testConfigure()
    {
        $this->instance->shouldReceive('setName')->with(m::type('string'))->once();
        $this->instance->configure();
    }

    public function testGetSetComparator()
    {
        $comparator = function(){};
        $this->instance->setComparator($comparator);

        $this->assertSame($comparator, $this->instance->getComparator());
    }

    public function testOutputHeader()
    {

    }
}
