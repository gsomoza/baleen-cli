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

namespace Baleen\Cli\CommandBus\Run\Execute;

use Baleen\Cli\CommandBus\Run\AbstractRunHandler;
use Baleen\Migrations\Exception\TimelineException;
use Baleen\Migrations\Migration\Options;
use Baleen\Migrations\Migration\Options\Direction;
use Baleen\Migrations\Migration\OptionsInterface;
use Baleen\Migrations\Service\DomainBus\Migrate\Single\SingleCommand;
use Baleen\Migrations\Version;
use Baleen\Migrations\Version\VersionInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Class ExecuteHandler.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class ExecuteHandler extends AbstractRunHandler
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

        // build options
        $options = $this->buildOptions(
            $input->getArgument(ExecuteMessage::ARG_DIRECTION),
            $input->getOption(ExecuteMessage::OPT_DRY_RUN)
        );
        $direction = $options->getDirection();

        // get target (we use the collection to allow alias resolution, e.g. "last")
        $target = $this->getTarget($message, $input->getArgument(ExecuteMessage::ARG_VERSION));
        $output->writeln('Target: ' . $helper->formatVersion($target));

        // if interactive, make sure the user confirms execution
        $canProceed = $this->askConfirmation($message, $direction, $output, $input);

        if ($canProceed) {
            $this->issueDomainCommand($target, $options);

            $finalMessage = "Version <comment>{$target->getId()}</comment> migrated <info>$direction</info> successfully.";
        } else {
            $finalMessage = "<comment>Aborted by user.</comment>";
        }

        $output->writeln($finalMessage);
    }

    /**
     * buildOptions
     *
     * @param $directionArg
     * @param $dryRunOption
     *
     * @return Options
     */
    protected function buildOptions($directionArg, $dryRunOption)
    {
        $direction = $directionArg == Direction::UP ?
            Direction::up() :
            Direction::down();

        $dryRun = (bool) $dryRunOption;
        $forced = true; // because we're executing a single migration
        $options = new Options($direction, $forced, $dryRun);

        return $options;
    }

    /**
     * getTarget
     *
     * @param ExecuteMessage $message
     *
     * @param string $versionArg
     *
     * @return VersionInterface|null
     * @throws TimelineException
     */
    protected function getTarget(ExecuteMessage $message, $versionArg)
    {
        $collection = $message->getRepositories()->fetchAll();
        $targetKey = (string) $versionArg;
        $target = $collection->find($targetKey);
        if (null === $target) {
            throw new TimelineException(
                sprintf('Could not find a target with key "%s".', $targetKey)
            );
        }

        return $target;
    }

    /**
     * askConfirmation
     *
     * @param ExecuteMessage $message
     * @param $direction
     * @param $output
     * @param $input
     *
     * @return mixed
     */
    protected function askConfirmation(
        ExecuteMessage $message,
        Direction $direction,
        OutputInterface $output,
        InputInterface $input
    ) {
        if (!$input->isInteractive()) {
            return true;
        }

        $output->writeln(
            [
                '',
                '<error>' . str_repeat(' ', strlen('  WARNING!  ')) . '</error>',
                '<error>  WARNING!  </error> You\'re about to execute a database migration manually, which could ' .
                'result in schema changes and data lost.',
                '<error>' . str_repeat(' ', strlen('  WARNING!  ')) . '</error>',
                '',
            ]
        );
        $question = sprintf('<info>Are you sure you want to migrate this version "%s" (y/n)?</info> ', $direction);
        $canProceed = $message->getCliCommand()
            ->getHelper('question')
            ->ask($input, $output, new ConfirmationQuestion($question));

        return $canProceed;
    }

    /**
     * issueDomainCommand
     *
     * @param $target
     * @param $options
     *
     * @return void
     */
    protected function issueDomainCommand(VersionInterface $target, OptionsInterface $options)
    {
        $singleCommand = new SingleCommand($target, $options, $this->getStorage());
        $this->getDomainBus()->handle($singleCommand);
    }
}
