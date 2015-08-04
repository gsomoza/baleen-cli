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
 * <https://github.com/baleen/migrations>.
 */

namespace Baleen\Cli\Command\Repository;

use Baleen\Cli\Exception\CliException;
use Baleen\Migrations\Migration\SimpleMigration;
use League\Flysystem\Config;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\FileGenerator;
use Zend\Code\Generator\MethodGenerator;

/**
 * Class CreateCommand
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class CreateCommand extends AbstractRepositoryCommand
{
    const COMMAND_NAME = 'create';

    public function configure()
    {
        $this
            ->setDescription('Creates a new migration file.')
            ->addArgument('title', null, 'Adds a descriptive title for the migration file and class name', null)
            ->addOption('namespace', null, InputOption::VALUE_OPTIONAL, 'Overrides the configured namespace', null)
            ->addOption('editor-cmd', null, InputOption::VALUE_OPTIONAL, 'Open file with this command upon creation.')
            ;
        parent::configure();
    }

    /**
     * @inheritDoc
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $directory = $this->config->getMigrationsDirectory();
        if (!$this->getFilesystem()->has($directory)) {
            throw new CliException(sprintf(
                'Migrations directory "%s" does not exist.',
                $directory
            ));
        }

        $namespace = $input->getOption('namespace');
        $editorCmd = $input->getOption('editor-cmd');

        $timestamp = date('YmdHis');
        if (null === $namespace) {
            $namespace = $this->config->getMigrationsNamespace();
        }
        $namespace = rtrim($namespace, '\\');

        $title = $input->getArgument('title');
        $title = preg_replace('/[^A-Za-z\d_]+/', '', $title);
        $className = ['v' . $timestamp];
        if (!empty($title)) {
            $className[] = $title;
        }

        $result = $this->generate(implode('_', $className), $namespace);
        if ($result) {
            $output->writeln(sprintf(
                'Created new Migration file at "<info>./%s</info>"',
                $result
            ));
            if ($editorCmd) {
                proc_open($editorCmd . ' ' . escapeshellarg($result), array(), $pipes);
            }
        } else {
            $output->writeln(
                'An error occurred creating a new Migration file. Please check directory permissions and configuration.'
            );
        }
        return $result;
    }

    /**
     * @param string $className
     * @param string $namespace
     * @return string|false
     * @throws CliException
     */
    protected function generate($className, $namespace = null)
    {
        $class = new ClassGenerator(
            $className,
            $namespace,
            null,
            'SimpleMigration',
            [],
            [],
            [
                new MethodGenerator('up', [], 'public', 'echo \'Hello world!\';'),
                new MethodGenerator('down', [], 'public', 'echo \'Goodbye world!\';'),
            ]
        );
        $class->addUse(SimpleMigration::class);
        return $this->writeClass($class);
    }

    /**
     * @param ClassGenerator $class
     * @return array
     * @throws CliException
     */
    protected function writeClass(ClassGenerator $class)
    {
        $className = $class->getName();
        $file = new FileGenerator([
            'fileName' => $className . '.php',
            'classes' => [$class]
        ]);
        $relativePath = $this->config->getMigrationsDirectory() . DIRECTORY_SEPARATOR . $file->getFilename();
        $contents = $file->generate();
        if ($this->getFilesystem()->has($relativePath)) {
            throw new CliException(sprintf(
                'Could not generate migration. File already exists: %s', $relativePath
            ));
        }
        $result = $this->getFilesystem()->write($relativePath, $contents);
        return $result ? $relativePath : false;
    }
}
