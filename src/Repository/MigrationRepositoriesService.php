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

use Baleen\Cli\Exception\CliException;
use Baleen\Migrations\Migration\Repository\MigrationRepositoryInterface;
use Baleen\Migrations\Shared\Collection\CollectionInterface;
use Baleen\Migrations\Version\Collection\Collection;
use Baleen\Migrations\Version\Comparator\ComparatorInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class MigrationRepositoriesService
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 *
 * @method MigrationRepositoryInterface first()
 * @method MigrationRepositoryInterface last()
 * @method MigrationRepositoryInterface next()
 * @method MigrationRepositoryInterface current()
 * @method MigrationRepositoryInterface offsetGet($offset)
 * @method MigrationRepositoryInterface offsetUnset($offset)
 * @method MigrationRepositoryInterface[] toArray()
 * @method MigrationRepositoryInterface[] getValues()
 * @property MigrationRepositoryInterface[] elements
 */
final class MigrationRepositoriesService extends ArrayCollection implements MigrationRepositoriesServiceInterface
{
    /** @var ComparatorInterface */
    private $comparator;

    /** @var array */
    private $cache = [];

    /**
     * MigrationRepositoriesService constructor.
     *
     * @param ComparatorInterface $comparator
     */
    public function __construct(ComparatorInterface $comparator)
    {
        $this->comparator = $comparator;
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    public function add($value)
    {
        $this->validate($value);
        return parent::add($value);
    }

    /**
     * @inheritDoc
     */
    public function set($key, $value)
    {
        $this->validate($value);
        parent::set($key, $value);
    }

    /**
     * Fetch all versions for the specified repository, or for all repositories at once if $key is null.
     *
     * @param null|int|string $key
     *
     * @return CollectionInterface
     *
     * @throws CliException
     */
    public function fetchAll($key = null)
    {
        $collection = new Collection([], null, $this->comparator);
        if (null !== $key) {
            $repo = $this->get($key);
            if (null === $repo) {
                throw new CliException(sprintf(
                    'Repository "%s" does not exist. See your configuration file for a list of available repositories ' .
                    'and their aliases.',
                    $key
                ));
            }
            $repositories = [$repo];
        } else {
            $repositories = $this->toArray();
        }
        foreach ($repositories as $repo) {
            $hash = spl_object_hash($repo);
            if (!empty($this->cache[$hash])) {
                $versions = $this->cache[$hash];
            } else {
                $versions = $repo->fetchAll();
                $this->cache[$hash] = $versions;
            }
            $collection->merge($versions);
        }
        return $collection;
    }

    /**
     * clearCache
     *
     * @param null|int|string $forRepo Clear for a specific repo only
     */
    public function clearCache($forRepo = null)
    {
        if (null !== $forRepo) {
            $repo = $this->get($forRepo);
            $hash = spl_object_hash($repo);
            if (isset($this->cache[$hash])) {
                unset($this->cache[$hash]);
            }
        } else {
            $this->cache = [];
        }
    }

    /**
     * Validates that a value can be added to the collection
     *
     * @param $value
     *
     * @throws CliException
     */
    protected function validate($value) {
        if (!is_object($value) || !$value instanceof MigrationRepositoryInterface) {
            throw new CliException(sprintf(
                'Expected value to be an instance of "%s". Got "%s" instead.',
                MigrationRepositoryInterface::class,
                is_object($value) ? get_class($value) : gettype($value)
            ));
        }
    }
}
