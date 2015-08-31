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
 * <https://github.com/baleen/migrations>.
 */

namespace Baleen\Cli\Command\Timeline;

use Baleen\Cli\Command\AbstractCommand;
use Baleen\Cli\Command\Util\StorageAwareInterface;
use Baleen\Cli\Command\Util\StorageAwareTrait;
use Baleen\Cli\Command\Util\TimelineAwareInterface;
use Baleen\Cli\Command\Util\TimelineAwareTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class AbstractTimelineCommand.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
abstract class AbstractTimelineCommand extends AbstractCommand implements StorageAwareInterface, TimelineAwareInterface
{
    use StorageAwareTrait;
    use TimelineAwareTrait;

    const OPT_DRY_RUN = 'dry-run';
    const OPT_NO_STORAGE = 'no-storage';

    /**
     * @inheritDoc
     */
    public static function configure(Command $command)
    {
        $command->addOption(self::OPT_DRY_RUN, 'd', InputOption::VALUE_NONE, 'Execute the migration on dry-run mode.')
            ->addOption(
                self::OPT_NO_STORAGE,
                null,
                InputOption::VALUE_NONE,
                'Do not persist execution results to storage.'
            );
    }
}
