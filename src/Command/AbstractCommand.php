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

namespace Baleen\Baleen\Command;

use Baleen\Baleen\Config\AppConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AbstractCommand
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class AbstractCommand extends Command
{
    const COMMAND_NAME = '';

    /** @var AppConfig */
    protected $config;

    public function setConfig(AppConfig $config)
    {
        $this->config = $config;
    }

    public function configure()
    {
        $this->setName(static::COMMAND_NAME);
        $this->addOption(
            'configuration',
            null,
            InputOption::VALUE_OPTIONAL,
            'The path to a migrations configuration file.'
        );
    }

    protected function outputHeader($configuration, OutputInterface $output)
    {
        $name = $configuration->getName();
        $name = $name ? $name : 'Doctrine Database Migrations';
        $name = str_repeat(' ', 20) . $name . str_repeat(' ', 20);
        $output->writeln('<question>' . str_repeat(' ', strlen($name)) . '</question>');
        $output->writeln('<question>' . $name . '</question>');
        $output->writeln('<question>' . str_repeat(' ', strlen($name)) . '</question>');
        $output->writeln('');
    }
    
}
