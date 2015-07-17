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

namespace BaleenTest\Baleen;

use Baleen\Cli\Application;
use Mockery as m;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\HelperSet;

/**
 * Class ApplicationTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class ApplicationTest extends BaseTestCase
{

    public function testConstructor()
    {
        $commands = [new Command('test')];
        $helperSet = m::mock(HelperSet::class);
        $instance = new Application($commands, $helperSet);
        $this->assertEquals('Baleen', $instance->getName());

        // here's how we can test that the init() function got called in the constructor
        $this->assertSame($helperSet, $instance->getHelperSet());
    }

    public function testInit()
    {
        $commands = [new Command('test')];
        $helperSet = m::mock(HelperSet::class);
        $instance = m::mock(Application::class)
            ->makePartial();
        $instance->shouldReceive('setCatchExceptions')->with(true)->once();
        $instance->shouldReceive('setHelperSet')->with($helperSet)->once();
        $instance->shouldReceive('addCommands')->with($commands)->once();
        $this->invokeMethod('init', $instance, [$commands, $helperSet]);
    }
}
