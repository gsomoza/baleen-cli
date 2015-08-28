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

namespace BaleenTest\Baleen\Command\Timeline;

use Baleen\Cli\Command\AbstractCommand;
use Baleen\Cli\Command\Timeline\AbstractTimelineCommand;
use Baleen\Cli\Command\Util\StorageAwareInterface;
use Baleen\Cli\Command\Util\TimelineAwareInterface;
use Baleen\Migrations\Storage\StorageInterface;
use Baleen\Migrations\Timeline;
use BaleenTest\Baleen\BaseTestCase;
use Mockery as m;

/**
 * Class AbstractTimelineCommandTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class AbstractTimelineCommandTest extends BaseTestCase
{
    /** @var m\Mock|AbstractTimelineCommand */
    protected $instance;

    /**
     * setUp
     */
    public function setUp()
    {
        $this->instance = m::mock(AbstractTimelineCommand::class)->makePartial();
    }

    /**
     * testConstructor
     */
    public function testConstructor()
    {
        $this->assertInstanceOf(AbstractCommand::class, $this->instance);
        $this->assertInstanceOf(StorageAwareInterface::class, $this->instance);
        $this->assertInstanceOf(TimelineAwareInterface::class, $this->instance);
    }

    /**
     * testGetSetStorage
     */
    public function testGetSetStorage()
    {
        $storage = m::mock(StorageInterface::class);
        $this->instance->setStorage($storage);
        $this->assertSame($storage, $this->instance->getStorage());
    }

    /**
     * testGetSetTimeline
     */
    public function testGetSetTimeline()
    {
        $timeline = m::mock(Timeline::class);
        $this->instance->setTimeline($timeline);
        $this->assertSame($timeline, $this->instance->getTimeline());
    }
}
