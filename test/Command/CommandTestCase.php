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

use BaleenTest\Baleen\BaseTestCase;
use Mockery as m;
use Symfony\Component\Console\Command\Command;

/**
 * Class CommandTestCase
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
abstract class CommandTestCase extends BaseTestCase
{
    /**
     * testConfigure
     */
    public function testConfigure() {
        /** @var m\Mock|Command $command */
        $command = m::mock(Command::class);

        $expectations = $this->getExpectations();
        foreach ($expectations as $func => $expectation) {
            $func = isset($expectation['name']) ? $expectation['name'] : $func;
            $times = isset($expectation['times']) ? $expectation['times'] : 1;
            $with = !empty($expectation['with']) ? $expectation['with'] : null;
            $return = isset($expectations['return']) ? $expectations['return'] : '!self';
            $exp = $command->shouldReceive($func);
            if (null !== $times) {
                $exp = $exp->times($times);
            }
            if (null !== $with) {
                if (!is_array($with)) {
                    $with = [$with];
                }
                $exp = call_user_func_array([$exp, 'with'], $with);
            }
            switch ($return) {
                case '!self':
                    $exp->andReturnSelf();
                    break;
                case '!null':
                case null:
                    $exp->andReturnNull();
                    break;
                default:
                    if (is_array($return)) {
                        $exp->andReturnValues($return);
                    } elseif (is_callable($return)) {
                        $exp->andReturnUsing($return);
                    }
                    break;
            }
        }
        forward_static_call([$this->getCommandClass(), 'configure'], $command);
    }

    /**
     * Must test the constructor and assert implemented interfaces
     */
    abstract public function testConstructor();

    /**
     * getCommandClass must return a string with the FQN of the command class being tested
     * @return string
     */
    abstract protected function getCommandClass();

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
    abstract protected function getExpectations();
}
