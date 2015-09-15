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

namespace Baleen\Cli\CommandBus\Timeline;

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
    public function handle(ExecuteMessage $command)
    {
        $input = $command->getInput();
        $output = $command->getOutput();
        $version = (string) $input->getArgument(ExecuteMessage::ARG_VERSION);

        $direction = $input->getArgument(ExecuteMessage::ARG_DIRECTION) == Options::DIRECTION_DOWN ?
            Options::DIRECTION_DOWN :
            Options::DIRECTION_UP;
        $dryRun = (bool) $input->getOption(ExecuteMessage::OPT_DRY_RUN);
        $forced = true; // because we're executing a single migration

        $options = new Options($direction, $forced, $dryRun);

        $canExecute = true;
        if ($input->isInteractive()) {
            $output->writeln('<error>WARNING!</error> You are about to manually execute a database migration that '.
                'could result in schema changes and data lost.');
            $question = sprintf('Are you sure you wish to migrate "%s" (y/n)? ', $direction);
            $canExecute = $command->getCliCommand()
                ->getHelper('question')
                ->ask($input, $output, new ConfirmationQuestion($question));
        }
        if ($canExecute) {
            $result = $command->getTimeline()->runSingle($version, $options);
            if ($result && !$options->isDryRun()) {
                $version = $result;
                $command->getStorage()->update($version);
            }
            $output->writeln("Version <info>$version</info> migrated <info>$direction</info> successfully.");
        }
    }
}
