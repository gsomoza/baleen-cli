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

use Baleen\Migrations\Exception\InvalidArgumentException;
use Baleen\Migrations\Migration\Repository\Mapper\DefinitionInterface;
use Baleen\Migrations\Migration\Repository\Mapper\MigrationMapperInterface;

/**
 * Class MigrationMapperService
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
final class MigrationMapperService
{
    /** @var MigrationMapperInterface[] */
    private $mappers;

    /**
     * MigrationMapperService constructor.
     *
     * @param MigrationMapperInterface[] $mappers
     *
     * @throws InvalidArgumentException
     */
    public function __construct(array $mappers)
    {
        foreach ($mappers as $mapper) {
            if (!$mapper instanceof MigrationMapperInterface) {
                throw InvalidArgumentException::invalidObjectException($mapper, MigrationMapperInterface::class);
            }
        }
        $this->mappers = $mappers;
    }

    /**
     * fetchAll
     *
     * @param null|string $dir
     *
     * @return DefinitionInterface[]
     */
    public function fetchAllDefinitions($dir = null)
    {
        $definitions = [];
        foreach ($this->getMappers($dir) as $mapper) {
            $definitions = array_merge($definitions, $mapper->fetchAll());
        }
        return $definitions;
    }

    /**
     * getMappers
     *
     * @param string|null $dir
     *
     * @return MigrationMapperInterface[]
     */
    public function getMappers($dir = null)
    {
        if (null === $dir) {
            return $this->mappers;
        }

        if (isset($this->mappers[$dir])) {
            return $this->mappers[$dir];
        }

        return [];
    }
}
