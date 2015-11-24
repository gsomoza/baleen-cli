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
final class MigrationRepositoryServiceFactory
{
    /**
     * @var ComparatorInterface
     */
    private $comparator;

    /** @var VersionRepositoryInterface */
    private $storage;

    /** @var MigrationMapperService */
    private $mapperService;

    /**
     * MigrationRepositoryServiceFactory constructor.
     *
     * @param MigrationMapperService $mapperService
     * @param VersionRepositoryInterface $versionRepository
     * @param ComparatorInterface $comparator
     */
    public function __construct(
        MigrationMapperService $mapperService,
        VersionRepositoryInterface $versionRepository,
        ComparatorInterface $comparator
    ) {
        $this->comparator = $comparator;
        $this->storage = $versionRepository;
        $this->mapperService = $mapperService;
    }

    /**
     * Create
     *
     * @return MigrationRepositoriesService
     *
     * @throws CliException
     */
    public function create() {
        $repositories = new MigrationRepositoriesService($this->comparator);

        $mappers = $this->mapperService->getMappers();
        foreach ($mappers as $alias => $mapper) {
            $repo = new MigrationRepository($this->storage, $mapper, $this->comparator);
            $repositories->set($alias, $repo);
        }

        return $repositories;
    }
}
