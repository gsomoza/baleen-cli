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

namespace BaleenTest\Cli\CommandBus\Config;

use Baleen\Cli\CommandBus\AbstractMessage;
use Baleen\Cli\CommandBus\Config\InitMessage;
use Baleen\Cli\CommandBus\Util\ConfigStorageAwareInterface;
use Baleen\Cli\Config\ConfigStorage;
use BaleenTest\Cli\CommandBus\MessageTestCase;
use Mockery as m;

/**
 * Class InitMessageTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class InitMessageTest extends MessageTestCase
{
    /**
     * testConstructor
     */
    public function testConstructor()
    {
        $instance = new InitMessage();
        $this->assertInstanceOf(AbstractMessage::class, $instance);
        $this->assertInstanceOf(ConfigStorageAwareInterface::class, $instance);
    }

    /**
     * testGetSetConfigStorage
     */
    public function testGetSetConfigStorage()
    {
        $instance = new InitMessage();
        /** @var ConfigStorage $configStorage */
        $configStorage = m::mock(ConfigStorage::class);
        $instance->setConfigStorage($configStorage);
        $this->assertSame($configStorage, $instance->getConfigStorage());
    }

    /**
     * @inheritdoc
     */
    protected function getExpectations()
    {
        return [
            'setName' => [
                'with' => 'config:init',
            ],
            'setAliases' => [
                'with' => [['init']],
            ],
            [   'name' => 'setDescription',
                'with' => m::type('string'),]
        ];
    }

    /**
     * @inheritdoc
     */
    protected function getCommandClass()
    {
        return InitMessage::class;
    }
}
