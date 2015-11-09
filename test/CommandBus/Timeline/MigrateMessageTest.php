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

use Baleen\Cli\CommandBus\Timeline\AbstractTimelineCommand;
use Baleen\Cli\CommandBus\Timeline\MigrateMessage;
use Baleen\Migrations\Version;
use BaleenTest\Cli\CommandBus\MessageTestCase;
use Mockery as m;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class MigrateMessageTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class MigrateMessageTest extends MessageTestCase
{

    /**
     * Must test the constructor and assert implemented interfaces
     */
    public function testConstructor()
    {
        $instance = new MigrateMessage();
        $this->assertInstanceOf(AbstractTimelineCommand::class, $instance);
    }

    /**
     * getClassName must return a string with the FQN of the command class being tested
     * @return string
     */
    protected function getClassName()
    {
        return MigrateMessage::class;
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
                'with' => 'timeline:migrate',
            ],
            [   'name' => 'setAliases',
                'with' => [['migrate']],
            ],
            [   'name' => 'setDescription',
                'with' => m::type('string'),
            ],
            [   'name' => 'addArgument',
                'with' => [MigrateMessage::ARG_TARGET, InputArgument::OPTIONAL, m::type('string'), 'latest'],
            ],
            ['name' => 'addOption',
             'with' => [MigrateMessage::OPT_PROGRESS, m::any(), InputOption::VALUE_NONE, m::type('string')],
            ],
            [   'name' => 'addOption',
                'with' => [MigrateMessage::OPT_STRATEGY, 's', InputOption::VALUE_REQUIRED, m::type('string'), 'up'],
            ],
            [   'name' => 'addOption',
                'with' => [MigrateMessage::OPT_DRY_RUN, 'd', InputOption::VALUE_NONE, m::type('string')],
            ],
            [   'name' => 'addOption',
                'with' => [MigrateMessage::OPT_NO_STORAGE, m::any(), InputOption::VALUE_NONE, m::type('string')],
            ],
        ];
    }
}
