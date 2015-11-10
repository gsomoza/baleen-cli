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

namespace Baleen\Cli\CommandBus\Config\Status;

use Baleen\Cli\CommandBus\AbstractHelper;
use Baleen\Cli\Helper\VersionFormatter;
use Baleen\Migrations\Version\Collection;
use Baleen\Migrations\Version\VersionInterface;

/**
 * Class StatusHelper
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
final class StatusHelper extends AbstractHelper
{
    const STYLE_INFO = 'info';
    const STYLE_COMMENT = 'comment';

    /**
     * Returns a list of versions that haven't been migrated, optionally scoped down to a single repository.
     *
     * @param null $repository
     *
     * @return Collection
     */
    public function getPending($repository = null)
    {
        /** @var StatusMessageInterface $message */
        $message = $this->getMessage();
        if (null !== $repository) {
            $repository = (string) $repository;
        }

        $migrated = $message->getStorage()->fetchAll();

        // get available repos, optionally scoped down to the repository passed as an option
        $available = $message->getRepositories()->fetchAll($repository)->hydrate($migrated);

        // find versions that haven't been migrated
        $pending = $available->filter(function (VersionInterface $v) {
            return !$v->isMigrated();
        });

        return $pending;
    }

    /**
     * Returns the HEAD version
     *
     * @return VersionInterface|null
     */
    public function getHead()
    {
        /** @var StatusMessageInterface $message */
        $message = $this->getMessage();
        $migrated = $message->getStorage()->fetchAll();
        // there's a single head across all repositories, so it must be fetched considering all repositories at once
        return $message->getRepositories()->fetchAll()->hydrate($migrated)->get('HEAD');
    }

    /**
     * Partitions a collection of versions into two collections: the first one with versions that are ordered "before"
     * the HEAD version, and the second one with versions that are ordered "after" the HEAD version.
     *
     * @param null|VersionInterface $head
     * @param Collection $pending
     *
     * @return Collection[]
     */
    public function getBeforeAndAfterHead(Collection $pending, $head)
    {
        if (null !== $head && $head instanceof VersionInterface) {
            /** @var StatusMessageInterface $message */
            $message = $this->getMessage();
            $comparator = $message->getComparator();
            list($beforeHead, $afterHead) = $pending->partition(
                function ($index, VersionInterface $v) use ($head, $comparator) {
                    return $comparator($v, $head) < 0;
                }
            );
            /** @var Collection $beforeHead */
            /** @var Collection $afterHead */
            $afterHead->removeElement($head);
        } else {
            $beforeHead = new Collection();
            $afterHead = $pending;
        }
        return [$beforeHead, $afterHead];
    }

    /**
     * Prints an array (group) of Versions all with the given style. If the array is empty then it prints nothing.
     *
     * @param Collection $collection
     * @param string|string[] $messages Message(s) to print before the group of versions.
     * @param string $versionStyle One of the STYLE_* constants.
     *
     * @return array|void
     */
    public function getCollectionMessages(
        Collection $collection,
        $messages,
        $versionStyle = self::STYLE_COMMENT
    ) {
        $outputHelper = new StatusOutputHelper($this->getRepositoryOption());
        /** @var VersionFormatter $formatter */
        $formatter = $this->getMessage()->getCliCommand()->getHelper('versionFormatter');
        $output = [];
        if ($collection->isEmpty()) {
            return [];
        }

        foreach ($messages as $key => $args) {
            if (!is_array($args)) {
                $key = $args;
            }
            $output[] = $outputHelper->get($key);
        }

        $output[] = '';

        $lines = $formatter->formatCollection($collection, $versionStyle);
        foreach ($lines as &$line) {
            $output[] = "\t" . $line;
        }

        return $output;
    }

    /**
     * getRepositoryOption
     * @return mixed
     */
    public function getRepositoryOption()
    {
        return $this->getMessage()->getInput()->getOption(StatusMessage::OPTION_REPOSITORY);
    }
}
