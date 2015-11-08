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

namespace Baleen\Cli\CommandBus\Config;

use Baleen\Cli\Exception\CliException;
use Baleen\Cli\Util\CalculatesRelativePathsTrait;
use Baleen\Migrations\Version;
use Baleen\Migrations\Version\VersionInterface;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Handles the config:status command.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class StatusHandler
{
    use CalculatesRelativePathsTrait;

    const STYLE_INFO = 'info';
    const STYLE_COMMENT = 'comment';

    /** @var OutputInterface */
    protected $output;

    /** @var StatusMessage */
    protected $message;

    /**
     * Handles a StatusMessage, which prints the status of the migrations system in a developer-friendly format
     * (inspired by "git status").
     *
     * @param StatusMessage $message
     */
    public function handle(StatusMessage $message)
    {
        $this->message = $message;
        $this->output = $message->getOutput();
        $output = $this->output;

        $repository = $message->getRepository();
        $storage = $message->getStorage();

        $available = $repository->fetchAll();
        $migrated = $storage->fetchAll();
        $pending = $available->filter(function(VersionInterface $v) use ($migrated) {
            return $migrated->get($v) === null;
        });

        $head = $migrated->last();
        $currentMessage = $head ?
            "Current version: <comment>{$head->getId()}</comment>" :
            'Nothing has been migrated yet.';
        $output->writeln($currentMessage);

        $pendingCount = $pending->count();

        if ($pendingCount > 0) {
            $executable = (defined('MIGRATIONS_EXECUTABLE') ? MIGRATIONS_EXECUTABLE . ' ' : '') . 'migrate';
            $output->writeln([
                "Your database is out-of-date by $pendingCount versions, and can be migrated.",
                sprintf(
                    '  (use "<comment>%s</comment>" to execute all migrations)',
                    $executable
                ),
                ''
            ]);
            if ($head) {
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
                $beforeHead = new Version\Collection();
                $afterHead = $pending;
            }
            $this->printCollection(
                $beforeHead,
                [
                    'Old migrations still pending:',
                    sprintf("  (use \"<comment>{$executable}migrate HEAD</comment>\" to migrate them)", $executable),
                ],
                self::STYLE_COMMENT
            );
            $this->printCollection($afterHead, ['New migrations:'], self::STYLE_INFO);
        } else {
            $output->writeln('Your database is up-to-date.');
        }
    }

    /**
     * Formats and prints a pending version with the given style.
     *
     * @param VersionInterface $version The Version to print.
     * @param string $style One of the STYLE_* constants.
     *
     * @throws CliException
     */
    protected function printPendingVersion(VersionInterface $version, $style)
    {
        if (!$version->getMigration()) {
            throw new CliException(sprintf(
                'Expected version "%s" to be associated with a migration class.',
                $version->getId()
            ));
        }
        $reflectionClass = new \ReflectionClass($version->getMigration());
        $absolutePath = $reflectionClass->getFileName();
        $fileName = $absolutePath ? $this->getRelativePath(getcwd(), $absolutePath) : '';
        $this->output->writeln("\t<$style>$fileName</$style>");
    }

    /**
     * Prints an array (group) of Versions all with the given style. If the array is empty then it prints nothing.
     *
     * @param Collection $collection
     * @param string|string[] $message Message(s) to print before the group of versions.
     * @param string $style One of the STYLE_* constants.
     *
     * @throws CliException
     */
    private function printCollection(Collection $collection, $message, $style = self::STYLE_INFO)
    {
        if ($collection->isEmpty()) {
            return;
        }
        $this->output->writeln($message);
        $this->output->writeln('');

        foreach ($collection as $version) {
            $this->printPendingVersion($version, $style);
        }

        // if there was at least one version in the array
        $this->output->writeln('');
    }
}
