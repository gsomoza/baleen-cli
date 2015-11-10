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

use Baleen\Cli\Util\CalculatesRelativePathsTrait;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Handles the config:status command.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class StatusHandler
{
    use CalculatesRelativePathsTrait;

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
        $helper = new StatusHelper($message);
        $output = $message->getOutput();
        $repositoryOption = $message->getInput()->getOption(StatusMessage::OPTION_REPOSITORY);
        $outputHelper = new StatusOutputHelper($repositoryOption);

        $head = $helper->getHead();
        $pending = $helper->getPending($repositoryOption);

        if (!empty($repositoryOption)) {
            $output->writeln("<info>Showing status for repository:</info> <comment>$repositoryOption</comment>");
        }

        $currentMessage = $head ?
            'Current version: ' . $helper->formatVersion($head) :
            'Nothing has been migrated yet.';
        $output->writeln($currentMessage);

        $pendingCount = $pending->count();
        if ($pendingCount > 0) {
            $output->writeln([
                '',
                $outputHelper->get(StatusOutputHelper::DB_OUTDATED, [$pendingCount]),
                $outputHelper->get(StatusOutputHelper::PENDING_COMMAND),
                ''
            ]);

            list($beforeHead, $afterHead) = $helper->getBeforeAndAfterHead($pending, $head);

            $output->writeln($helper->getCollectionMessages(
                $beforeHead,
                [StatusOutputHelper::MIGRATIONS_PENDING, StatusOutputHelper::HEAD_COMMAND]
            ));

            $output->writeln($helper->getCollectionMessages(
                $afterHead,
                [StatusOutputHelper::MIGRATIONS_NEW],
                StatusHelper::STYLE_INFO
            ));
        } else {
            $output->writeln($outputHelper->get(StatusOutputHelper::DB_UPTODATE));
        }
    }
}
