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

use Baleen\Cli\Helper\VersionFormatter;
use Baleen\Cli\Helper\VersionFormatterInterface;
use Baleen\Cli\Util\CalculatesRelativePathsTrait;
use Baleen\Migrations\Version;
use Baleen\Migrations\Version\Collection;
use Baleen\Migrations\Version\VersionInterface;
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

        /** @var VersionFormatter $versionFormatter */
        $versionFormatter = $message->getCliCommand()->getHelper('versionFormatter');

        $repository = $message->getRepository();
        $storage = $message->getStorage();

        $available = $repository->fetchAll();
        $migrated = $storage->fetchAll();
        $pending = $available->filter(function (VersionInterface $v) use ($migrated) {
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
                $beforeHead = new Collection();
                $afterHead = $pending;
            }

            $this->printCollection(
                $versionFormatter,
                $beforeHead,
                [
                    'Old migrations still pending:',
                    sprintf("  (use \"<comment>{$executable} HEAD</comment>\" to migrate them)", $executable),
                ],
                self::STYLE_COMMENT
            );
            $this->printCollection($versionFormatter, $afterHead, ['New migrations:'], self::STYLE_INFO);
        } else {
            $output->writeln('Your database is up-to-date.');
        }
    }

    /**
     * Prints an array (group) of Versions all with the given style. If the array is empty then it prints nothing.
     *
     * @param VersionFormatterInterface $formatter
     * @param Collection $collection
     * @param string|string[] $message Message(s) to print before the group of versions.
     * @param string $style One of the STYLE_* constants.
     */
    protected function printCollection(
        VersionFormatterInterface $formatter,
        Collection $collection,
        $message,
        $style = self::STYLE_INFO
    ) {
        if ($collection->isEmpty()) {
            return;
        }
        $this->output->writeln($message);
        $this->output->writeln('');

        $lines = $formatter->formatCollection($collection, $style);
        foreach ($lines as &$line) {
            $line = "\t" . $line;
        }
        $lines[] = '';

        $this->output->writeln($lines);
    }
}
