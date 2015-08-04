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
use Baleen\Cli\Config\AppConfig;
use Baleen\Cli\Exception\CliException;
use Baleen\Migrations\Migration\SimpleMigration;
use BaleenTest\Baleen\Command\CommandTestCase;
use League\Flysystem\Adapter\NullAdapter;
use League\Flysystem\Filesystem;
use Mockery as m;
use Zend\Code\Generator\ClassGenerator;

/**
 * Class CreateCommandTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class CreateCommandTest extends CommandTestCase
{
    /** @var Filesystem */
    protected $filesystem;

    public function setUp()
    {
        parent::setUp();
        $this->instance = m::mock(CreateCommand::class)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();
        $this->filesystem = new Filesystem(new NullAdapter());
        $this->instance->setFilesystem($this->filesystem);
    }

    public function testConstructor()
    {
        $instance = new CreateCommand();
        $this->assertInstanceOf(AbstractRepositoryCommand::class, $instance);
        $this->assertNotEmpty(CreateCommand::COMMAND_NAME);
        $this->assertContains(CreateCommand::COMMAND_NAME, $instance->getName());
        $this->assertNotEmpty($instance->getDescription());
    }

    /**
     * @param $className
     * @param null $namespace
     * @dataProvider generateProvider
     */
    public function testGenerate($className, $namespace = null)
    {
        $expected = 'test';
        $self = $this;
        $this->instance
            ->shouldReceive('writeClass')
            ->with(m::on(function (ClassGenerator $generator) use ($self, $className, $namespace) {
                $self->assertEquals($className, $generator->getName());
                $self->assertEquals($namespace, $generator->getNamespaceName());
                $self->assertTrue(in_array(SimpleMigration::class, $generator->getUses()));
                $self->assertTrue($generator->hasMethod('up'));
                $self->assertTrue($generator->hasMethod('down'));
                return true;
            }))
            ->once()
            ->andReturn($expected);

        $result = $this->instance->generate($className, $namespace);

        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function generateProvider()
    {
        return [
            ['TestDefaults'],
            ['TestValidParams', 'TestNamespace'],
        ];
    }

    /**
     * testWriteClass
     * @dataProvider writeClassProvider
     */
    public function testWriteClass($has, $writeResult)
    {
        $filesystem = m::mock(Filesystem::class);
        $this->instance->setFilesystem($filesystem);

        $generator = new ClassGenerator();
        $className = 'TestClass';
        $generator->setName($className);

        $config = m::mock(AppConfig::class);
        $migrationsDir = 'migrations';
        $config->shouldReceive(['getMigrationsDirectory' => $migrationsDir]);

        $filePath = $migrationsDir . DIRECTORY_SEPARATOR . $className . '.php';
        $filesystem->shouldReceive('has')->with($filePath)->andReturn($has);
        if ($has) {
            $filesystem->shouldNotReceive('write');
            $this->setExpectedException(CliException::class, 'already exists');
        } else {
            $filesystem->shouldReceive('write')->with($filePath, m::type('string'))->andReturn($writeResult);
        }

        $this->instance->setConfig($config);
        $this->instance->writeClass($generator);
    }

    /**
     * @return array
     */
    public function writeClassProvider()
    {
        return [
            [true, true],
            [true, false],
            [false, true],
            [false, false],
        ];
    }

    /**
     * testExecuteNoMigrationsDirectory
     */
    public function testExecuteNoMigrationsDirectory()
    {
        $config = m::mock(AppConfig::class);
        $config->shouldReceive(['getMigrationsDirectory' => 'migrations']);
        $this->instance->setConfig($config);
        $this->setExpectedException(CliException::class, 'not exist');
        $this->execute();
    }

    /**
     * testExecute
     * @param $title
     * @param $namespace
     * @param $success
     * @param $editorCmd
     * @dataProvider executeProvider
     */
    public function testExecute($title, $namespace, $success, $editorCmd = null)
    {
        $filesystem = m::mock(Filesystem::class);
        $filesystem->shouldReceive(['has' => true]);
        $this->instance->setFilesystem($filesystem);

        $config = m::mock(AppConfig::class);
        $config->shouldReceive(['getMigrationsDirectory' => 'migrations']);
        $config->shouldReceive('getMigrationsNamespace')->zeroOrMoreTimes()->andReturn('DefaultNamespace');
        $this->instance->setConfig($config);

        $ns = key($namespace) ?: null;
        $this->input->shouldReceive('getOption')->with('namespace')->once()->andReturn();
        $this->input->shouldReceive('getOption')->with('editor-cmd')->once()->andReturn($editorCmd);
        $this->input->shouldReceive('getArgument')->with('title')->andReturn(key($title));

        $self = $this;
        $this->instance
            ->shouldReceive('generate')
            /*->with(m::on(function($className, $resultNamespace) use ($self, $title, $namespace) {
                $self->assertEquals(current($title), $className);
                $self->assertEquals(current($namespace), $resultNamespace);
                return true;
            }))*/
            ->andReturn($success);

        $with = null;
        if ($success) {
            $with = '/[Cc]reated/';
        } else {
            $with = '/error/';
        }
        $this->output->shouldReceive('writeln')->with($with)->once();

        $this->execute();
    }

    /**
     * @return array
     */
    public function executeProvider()
    {
        return [
            [ // simple test
                ['SimpleTitle' => 'SimpleTitle'],
                ['SimpleNamespace' => 'SimpleNamespace'],
                true,
            ],
            [ // simple test with editor cmd
                ['SimpleTitle' => 'SimpleTitle'],
                ['SimpleNamespace' => 'SimpleNamespace'],
                true,
                'which' // could be anything, but which has no output if the argument doesn't exist so its a good fit
            ],
            [ // test null namespace (load from config)
                ['SimpleTitle' => 'SimpleTitle'],
                [0 => 'SimpleNamespace'],
                true,
            ],
            [ // test generate failure
                ['SimpleTitle' => 'SimpleTitle'],
                ['SimpleNamespace' => 'SimpleNamespace'],
                false,
            ],
            [ // test title and namespace sanitizing
                ['I!n@v@a#l$i%d=_-+^T&i*t(l)e' => 'Invalid_Title'],
                ['I!n@v#a$l%i^d&*a(m)e_s-p+a=ce' => 'Invalid_Namespace'],
                true,
            ],
        ];
    }
}
