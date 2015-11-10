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
use Baleen\Migrations\Version\Collection\Linked;
use Baleen\Migrations\Version\Comparator\ComparatorInterface;

/**
 * Interface RepositoryCollection
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
interface RepositoryCollectionInterface
{
    /**
     * Fetch all versions for the specified repository, or for all repositories at once if $key is null.
     *
     * @param null|int|string $key
     *
     * @return Linked
     *
     * @throws CliException
     */
    public function fetchAll($key = null);

    /**
     * clearCache
     *
     * @param null|int|string $forRepo Clear for a specific repo only
     */
    public function clearCache($forRepo = null);

    /**
     * Constructor
     *
     * @param ComparatorInterface $comparator
     */
    public function __construct(ComparatorInterface $comparator);
}
