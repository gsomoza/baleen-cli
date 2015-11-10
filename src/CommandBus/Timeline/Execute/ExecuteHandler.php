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

namespace Baleen\Cli\CommandBus\Timeline\Execute;

use Baleen\Cli\CommandBus\Timeline\Execute\ExecuteMessage;
use Baleen\Cli\Helper\VersionFormatter;
use Baleen\Migrations\Exception\TimelineException;
use Baleen\Migrations\Migration\Options;
use Baleen\Migrations\Version;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Class ExecuteHandler.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class ExecuteHandler
{
    /**
     * {@inheritdoc}
     *
     * @param ExecuteMessage $message
     *
     * @throws \Baleen\Migrations\Exception\TimelineException
     */
    public function handle(ExecuteMessage $message)
    {
        $helper = new ExecuteHelper($message);
        $input = $message->getInput();
        $output = $message->getOutput();

        $factory = $message->getTimelineFactory();
        $available = $message->getRepositories()->fetchAll();
        $migrated = $message->getStorage()->fetchAll();
        $timeline = $factory->create($available, $migrated);

        $targetKey = (string) $input->getArgument(ExecuteMessage::ARG_VERSION);
        $target = $timeline->getVersions()->get($targetKey);
        if (null === $target) {
            throw new TimelineException(sprintf(
                'Could not find a target with key "%s".',
                $targetKey
            ));
        }

        $output->writeln('Target: ' . $helper->formatVersion($target));

        $direction = $input->getArgument(ExecuteMessage::ARG_DIRECTION) == Options::DIRECTION_DOWN ?
            Options::DIRECTION_DOWN :
            Options::DIRECTION_UP;
        $dryRun = (bool) $input->getOption(ExecuteMessage::OPT_DRY_RUN);
        $forced = true; // because we're executing a single migration

        $options = new Options($direction, $forced, $dryRun);

        $canExecute = true;
        if ($input->isInteractive()) {
            $output->writeln([
                '',
                '<error>'.str_repeat(' ', strlen('  WARNING!  ')) . '</error>',
                "<error>  WARNING!  </error> You're about to execute a database migration manually, which could result " .
                "in schema changes and data lost.",
                '<error>'.str_repeat(' ', strlen('  WARNING!  ')) . '</error>',
                '',
            ]);
            $question = sprintf('<info>Are you sure you wish to migrate "%s" (y/n)?</info> ', $direction);
            $canExecute = $message->getCliCommand()
                ->getHelper('question')
                ->ask($input, $output, new ConfirmationQuestion($question));
        }
        if ($canExecute) {
            $result = $timeline->runSingle($target, $options);
            if ($result && !$options->isDryRun()) {
                $message->getStorage()->update($result);
            }
            $output->writeln("Version <comment>{$target->getId()}</comment> migrated <info>$direction</info> successfully.");
        }
    }
}
