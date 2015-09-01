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

namespace Baleen\Cli\Command\Config;

use Baleen\Cli\Command\AbstractCommand;
use Baleen\Cli\Command\Util\ConfigStorageAwareInterface;
use Baleen\Cli\Command\Util\ConfigStorageAwareTrait;
use Symfony\Component\Console\Command\Command;

/**
 * Class InitCommand.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class InitCommand extends AbstractCommand implements ConfigStorageAwareInterface
{
    use ConfigStorageAwareTrait;

    /**
     * @inheritdoc
     */
    public static function configure(Command $command)
    {
        $command->setName('config:init');
        $command->setAliases(['init']);
        $command->setDescription('Initialises Baleen by creating a config file in the current directory.');
    }
}