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

namespace BaleenTest\Cli\CommandBus\Repository;

use Baleen\Cli\CommandBus\Repository\CreateMessage;
use Baleen\Cli\CommandBus\Repository\CreateHandler;
use Baleen\Cli\Config\Config;
use Baleen\Cli\Exception\CliException;
use Baleen\Migrations\Migration\SimpleMigration;
use BaleenTest\Cli\CommandBus\HandlerTestCase;
use League\Flysystem\Filesystem;
use Mockery as m;
use Zend\Code\Generator\ClassGenerator;

/**
 * Class CreateHandlerTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class CreateHandlerTest extends HandlerTestCase
{
    /** @var m\Mock|Filesystem */
    protected $filesystem;

    /**
     * setUp
     */
    public function setUp()
    {
        $this->instance = m::mock(CreateHandler::class)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();
        $this->filesystem = m::mock(Filesystem::class);
        $this->command = m::mock(CreateMessage::class);
        $this->command->shouldReceive('getFilesystem')->zeroOrMoreTimes()->andReturn($this->filesystem);
        parent::setUp();
    }

    /**
     * @param $className
     * @param null $namespace
     * @dataProvider generateProvider
     */
    public function testGenerate($className, $namespace = null)
    {
        /** @var \Zend\Code\Generator\ClassGenerator $result */
        $result = $this->invokeMethod('generate', $this->instance, [$className, $namespace]);

        $this->assertEquals($className, $result->getName());
        $this->assertEquals($namespace, $result->getNamespaceName());
        $this->assertContains(SimpleMigration::class, $result->getUses());
        $this->assertEquals(
            substr(SimpleMigration::class, strrpos(SimpleMigration::class, '\\') + 1),
            $result->getExtendedClass(),
            sprintf(
                'Expected generated class to extend "%s"; got "%s" instead.',
                SimpleMigration::class,
                $result->getExtendedClass()
            )
        );
        $this->assertTrue($result->hasMethod('up'), 'Expected generated class to have method "up()"');
        $this->assertTrue($result->hasMethod('down'), 'Expected generated class to have method "down()"');
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
     * @param $has
     * @param $writeResult
     *
     * @dataProvider writeClassProvider
     */
    public function testWriteClass($has, $writeResult)
    {
        $generator = new ClassGenerator();
        $className = 'TestClass';
        $generator->setName($className);

        $migrationsDir = 'migrations';

        $filePath = $migrationsDir . DIRECTORY_SEPARATOR . $className . '.php';

        $this->filesystem->shouldReceive('has')->with($filePath)->andReturn($has);

        if ($has) {
            $this->filesystem->shouldNotReceive('write');
            $this->setExpectedException(CliException::class, 'already exists');
        } else {
            $this->filesystem->shouldReceive('write')->with($filePath, m::type('string'))->andReturn($writeResult);
        }

        $this->invokeMethod('writeClass', $this->instance, [$generator, $this->filesystem, $migrationsDir]);
    }

    /**
     * @return array
     */
    public function writeClassProvider()
    {
        $trueFalse = [true, false];
        return $this->combinations([$trueFalse, $trueFalse]);
    }

    /**
     * testHandleNoMigrationsDirectory
     */
    public function testHandleNoMigrationsDirectory()
    {
        $this->filesystem->shouldReceive('has')->once()->andReturn(false);
        $config = m::mock(Config::class);
        $config->shouldReceive(['getMigrationsDirectory' => 'migrations']);
        $this->command->shouldReceive('getConfig')->once()->andReturn($config);
        $this->setExpectedException(CliException::class, 'not exist');
        $this->handle();
    }

    /**
     * testHandle
     * @param $title
     * @param $namespace
     * @param $success
     * @param $editorCmd
     * @dataProvider handleProvider
     */
    public function testHandle($title, $namespace, $success, $editorCmd = null)
    {
        $migrationsDirectory = 'migrations';
        $this->filesystem->shouldReceive('has')->once()->with($migrationsDirectory)->andReturn(true);

        $config = m::mock(Config::class);
        $config->shouldReceive(['getMigrationsDirectory' => $migrationsDirectory])->once();
        if ($editorCmd) {
            $config->shouldReceive('getMigrationsDirectoryPath')->once()->andReturn($migrationsDirectory);
        }
        $this->command->shouldReceive('getConfig')->once()->andReturn($config);

        $argumentNamespace = key($namespace) ?: null;
        if (null === $argumentNamespace) {
            $config->shouldReceive(['getMigrationsNamespace' => 'DefaultNamespace'])->once();
            $expectedNamespace = 'DefaultNamespace';
        } else {
            $expectedNamespace = current($namespace);
        }
        $this->input->shouldReceive('getOption')->with('namespace')->once()->andReturn($argumentNamespace);
        $this->input->shouldReceive('getOption')->with('editor-cmd')->once()->andReturn($editorCmd);

        $argumentTitle = key($title);
        $expectedTitle = current($title);
        $this->input->shouldReceive('getArgument')->with('title')->andReturn($argumentTitle);

        $titleRegex = !empty($argumentTitle) ? "_$expectedTitle" : '';
        $expectedRegex = "/v[0-9]+$titleRegex/";

        $generateResult = m::mock(ClassGenerator::class);
        $this->instance
            ->shouldReceive('generate')
            ->once()
            ->with($expectedRegex, $expectedNamespace)
            ->andReturn($generateResult);

        $this->instance
            ->shouldReceive('writeClass')
            ->once()
            //->with($generateResult, $this->filesystem, $migrationsDirectory)
            ->andReturn($success);

        $with = null;
        if ($success) {
            $with = '/[Cc]reated/';
        } else {
            $with = '/error/';
        }
        $this->output->shouldReceive('writeln')->with($with)->once();

        $this->handle();
    }

    /**
     * @return array
     */
    public function handleProvider()
    {
        return [
            [ // simple test
                ['SimpleTitle' => 'SimpleTitle'],
                ['SimpleNamespace' => 'SimpleNamespace'],
                true, // success
            ],
            [ // simple test with editor cmd
                ['SimpleTitle' => 'SimpleTitle'],
                ['SimpleNamespace' => 'SimpleNamespace'],
                true,
                'which' // FIXME: it would be better to test whether this gets called
            ],
            [ // test null namespace (load from config)
                ['SimpleTitle' => 'SimpleTitle'],
                [0 => 'SimpleNamespace'],
                true,
            ],
            [ // test write failure
                ['SimpleTitle' => 'SimpleTitle'],
                ['SimpleNamespace\\' => 'SimpleNamespace'], // also test trailing \ in namespace name
                false,
            ],
            [ // test title and namespace sanitizing
                ['I!n@v@a#l$i%d=_-+^T&i*t(l)e' => 'Invalid_Title'],
                ['I!n@v#a$l%i^d&_¶N*a(m)e∞s-p+a=ce' => 'Invalid_Namespace'],
                true,
            ],
        ];
    }
}
