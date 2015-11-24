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

namespace BaleenTest\Cli\CommandBus\Timeline;

use Baleen\Cli\CommandBus\Run\AbstractRunMessage;
use Baleen\Cli\CommandBus\Run\Execute\ExecuteMessage;
use Baleen\Migrations\Migration\Options;
use Baleen\Migrations\Migration\Options\Direction;
use Baleen\Migrations\Timeline;
use Baleen\Migrations\Version;
use BaleenTest\Cli\CommandBus\MessageTestCase;
use Mockery as m;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class ExecuteMessageTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class ExecuteMessageTest extends MessageTestCase
{

    /**
     * Must test the constructor and assert implemented interfaces
     */
    public function testConstructor()
    {
        $instance = new ExecuteMessage();
        $this->assertInstanceOf(\Baleen\Cli\CommandBus\Run\AbstractRunMessage::class, $instance);
    }

    /**
     * getClassName must return a string with the FQN of the command class being tested
     * @return string
     */
    protected function getClassName()
    {
        return ExecuteMessage::class;
    }

    /**
     * Must return an array in the format:
     *
     *      [
     *          'name' => 'functionName', // required
     *          'with' => [arguments for with] // optional
     *          'return' => return value // optional, defaults to return self
     *          'times' => number of times it will be invoked
     *      ]
     *
     * @return array
     */
    protected function getExpectations()
    {
        return [
            [   'name' => 'setName',
                'with' => 'timeline:execute',
            ],
            [   'name' => 'setAliases',
                'with' => [['exec', 'execute']],
            ],
            [   'name' => 'setDescription',
                'with' => m::type('string'),
            ],
            [   'name' => 'addOption',
                'with' => [ExecuteMessage::OPT_DRY_RUN, 'd', InputOption::VALUE_NONE, m::type('string')],
            ],
            [   'name' => 'addOption',
                'with' => [ExecuteMessage::OPT_NO_STORAGE, m::any(), InputOption::VALUE_NONE, m::type('string')],
            ],
            [   'name' => 'addArgument',
                'with' => [ExecuteMessage::ARG_VERSION, InputArgument::REQUIRED, m::type('string')],
            ],
            [   'name' => 'addArgument',
                'with' => [
                    ExecuteMessage::ARG_DIRECTION,
                    InputArgument::OPTIONAL,
                    m::type('string'),
                    Direction::UP,
                ],
            ],
        ];
    }
}
