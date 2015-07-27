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

namespace Baleen\Cli\Command;

use Baleen\Cli\Command\Util\HasConfigStorageInterface;
use Baleen\Cli\Command\Util\HasConfigStorageTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class InitCommand
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class InitCommand extends AbstractCommand implements HasConfigStorageInterface
{
    const COMMAND_NAME = 'init';

    use HasConfigStorageTrait;

    public function configure()
    {
        parent::configure();
        $this->setDescription('Initialises Baleen by creating a config file in the current directory.');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->getConfigStorage()->isInitialized()) {
            $output->writeln('Baleen is already initialised!');
            return;
        }

        $relativePath = $this->getConfigStorage()->getConfigFileName();
        $result = $this->getConfigStorage()->write();

        if ($result !== false) {
            $message = sprintf('Config file created at "<info>%s</info>".', $relativePath);
        } else {
            $message = sprintf(
                '<error>Error: Could not create and write file "<info>%s</info>". ' .
                'Please check file and directory permissions.</error>',
                $relativePath
            );
        }
        $output->writeln($message);
    }
}
