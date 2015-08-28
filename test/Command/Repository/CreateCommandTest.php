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

use Baleen\Cli\Command\Repository\AbstractRepositoryCommand;
use Baleen\Cli\Command\Repository\CreateCommand;
use Baleen\Cli\Config\Config;
use Baleen\Cli\Exception\CliException;
use Baleen\Migrations\Migration\SimpleMigration;
use BaleenTest\Baleen\Command\CommandTestCase;
use BaleenTest\Baleen\Command\HandlerTestCase;
use League\Flysystem\Adapter\NullAdapter;
use League\Flysystem\Filesystem;
use Mockery as m;
use Symfony\Component\Console\Input\InputOption;
use Zend\Code\Generator\ClassGenerator;

/**
 * Class CreateCommandTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class CreateCommandTest extends CommandTestCase
{
    /**
     * getCommandClass must return a string with the FQN of the command class being tested
     * @return string
     */
    protected function getCommandClass()
    {
        return CreateCommand::class;
    }

    /**
     * testConstructor
     */
    public function testConstructor()
    {
        $instance = new CreateCommand();
        $this->assertInstanceOf(AbstractRepositoryCommand::class, $instance);
    }

    /**
     * @inheritDoc
     */
    protected function getExpectations()
    {
        return [
            [   'name' => 'setName',
                'with' => 'migrations:create',
            ],
            [   'name' => 'setAliases',
                'with' => [['create']],
            ],
            [   'name' => 'setDescription',
                'with' => [m::type('string')],
            ],
            [   'name' => 'addArgument',
                'with' => ['title', m::any(), m::type('string'), m::any()]
            ],
            [   'name' => 'addOption',
                'with' => ['namespace', m::any(), InputOption::VALUE_OPTIONAL, m::type('string'), m::any()]
            ],
            [   'name' => 'addOption',
                'with' => ['editor-cmd', m::any(), InputOption::VALUE_OPTIONAL, m::type('string')]
            ],
        ];
    }


}
