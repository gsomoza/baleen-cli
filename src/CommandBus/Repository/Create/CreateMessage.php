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

namespace Baleen\Cli\CommandBus\Repository\Create;

use Baleen\Cli\CommandBus\Config\AbstractConfigMessage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class CreateMessage.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class CreateMessage extends AbstractConfigMessage
{
    const COMMAND_NAME = 'create';

    public static function configure(Command $command)
    {
        $command
            ->setName('migrations:create')
            ->setAliases(['create'])
            ->setDescription('Creates a new migration file.')
            ->addArgument('title', null, 'Adds a descriptive title for the migration file and class name', null)
            ->addOption('namespace', null, InputOption::VALUE_OPTIONAL, 'Overrides the configured namespace', null)
            ->addOption('editor-cmd', null, InputOption::VALUE_OPTIONAL, 'Open file with this command upon creation.');
    }
}
