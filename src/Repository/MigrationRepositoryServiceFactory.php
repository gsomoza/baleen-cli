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

namespace Baleen\Cli\Repository;

use Baleen\Cli\Config\ConfigInterface;
use Baleen\Cli\Exception\CliException;
use Baleen\Migrations\Migration\Factory\FactoryInterface;
use Baleen\Migrations\Migration\Repository\Mapper\DirectoryMapper;
use Baleen\Migrations\Migration\Repository\MigrationRepository;
use Baleen\Migrations\Version\Comparator\ComparatorInterface;
use Baleen\Migrations\Version\Repository\VersionRepositoryInterface;
use Composer\Autoload\ClassLoader;
use League\Flysystem\FilesystemInterface;

/**
 * Class MigrationRepositoryServiceFactory
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class MigrationRepositoryServiceFactory
{
    /**
     * @var ClassLoader
     */
    protected $autoloader;
    /**
     * @var string[]
     */
    private $migrationsConfig;
    /**
     * @var FactoryInterface
     */
    private $migrationFactory;
    /**
     * @var FilesystemInterface
     */
    private $filesystem;
    /**
     * @var ComparatorInterface
     */
    private $comparator;

    /** @var VersionRepositoryInterface */
    private $storage;

    /**
     * MigrationRepositoryServiceFactory constructor.
     *
     * @param ConfigInterface $config
     * @param VersionRepositoryInterface $versionRepository
     * @param FactoryInterface $migrationFactory
     * @param FilesystemInterface $filesystem
     * @param ComparatorInterface $comparator
     * @param ClassLoader $autoloader
     */
    public function __construct(
        ConfigInterface $config,
        VersionRepositoryInterface $versionRepository,
        FactoryInterface $migrationFactory,
        FilesystemInterface $filesystem,
        ComparatorInterface $comparator,
        ClassLoader $autoloader
    ) {
        $this->migrationsConfig = $config->getMigrationsConfig();
        $this->migrationFactory = $migrationFactory;
        $this->filesystem = $filesystem;
        $this->comparator = $comparator;
        $this->autoloader = $autoloader;
        $this->storage = $versionRepository;
    }

    /**
     * Create
     *
     * @return RepositoryCollection
     *
     * @throws CliException
     */
    public function create() {
        $repositories = new RepositoryCollection($this->comparator);
        foreach ($this->migrationsConfig as $priority => $config) {
            $dir = $config['directory'];

            $this->ensureDirectoryExists($dir);
            $this->configureAutoloader($config['namespace'], $dir);

            $mapper = new DirectoryMapper($dir, $this->migrationFactory);
            $repo = new MigrationRepository($this->storage, $mapper, $this->comparator);
            $alias = isset($config['alias']) ? $config['alias'] : $dir;
            $repositories->set($alias, $repo);
        }
        return $repositories;
    }

    /**
     * Checks if the directory exists and if not it creates it.
     *
     * @param $directory
     *
     * @return void
     * @throws CliException
     */
    protected function ensureDirectoryExists($directory)
    {
        $filesystem = $this->filesystem;
        if (!$filesystem->has($directory)) {
            $result = $filesystem->createDir($directory);
            if (!$result) {
                throw new CliException(
                    sprintf(
                        'Could not create directory "$s.',
                        $directory
                    )
                );
            }
        } else {
            $meta = $filesystem->getMetadata($directory);
            if ($meta['type'] !== 'dir') {
                throw new CliException(
                    sprintf(
                        'Expected path "%s" to be a directory, but its a %s.',
                        $directory,
                        $meta['type']
                    )
                );
            }
        }
    }

    /**
     * Makes sure classes in the migration directory are autoloaded
     *
     * @param string $ns
     * @param string $directory
     */
    protected function configureAutoloader($ns, $directory)
    {
        $autoloader = $this->autoloader;
        // normalize the namespace
        $ns = rtrim($ns, '\\') . '\\';
        // add to the autoloader if necessary
        if (!in_array($ns, $autoloader->getPrefixes())
            && !in_array($ns, $autoloader->getPrefixesPsr4())
        ) {
            $autoloader->addPsr4($ns, getcwd() . DIRECTORY_SEPARATOR . $directory);
        }
    }
}
