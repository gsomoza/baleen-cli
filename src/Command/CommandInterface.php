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

namespace Baleen\Cli\Command;

use Baleen\Cli\Config\ConfigInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Interface CommandInterface.
 */
interface CommandInterface
{
    /**
     * Configures a console command by setting name, description, arguments, etc.
     *
     * @param Command $command
     */
    public static function configure(Command $command);

    /**
     * getConfig.
     *
     * @return ConfigInterface
     */
    public function getConfig();

    /**
     * setConfig.
     *
     * @param ConfigInterface $config
     */
    public function setConfig(ConfigInterface $config);

    /**
     * getInput.
     *
     * @return InputInterface
     */
    public function getInput();

    /**
     * setInput.
     *
     * @param InputInterface $input
     */
    public function setInput(InputInterface $input);

    /**
     * getOutput.
     *
     * @return OutputInterface
     */
    public function getOutput();

    /**
     * setOutput.
     *
     * @param OutputInterface $output
     */
    public function setOutput(OutputInterface $output);

    /**
     * getCliCommand.
     *
     * @return Command
     */
    public function getCliCommand();

    /**
     * setCliCommand.
     *
     * @param Command $command
     */
    public function setCliCommand(Command $command);
}
