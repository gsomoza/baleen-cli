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

namespace Baleen\Cli\CommandBus\Config;

/**
 * Handles the config:init command.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class InitHandler
{
    /**
     * Handle an InitMessage. Creates an end-user configuration file using default values. If the file already exists
     * it simply exists without doing anything.
     *
     * @param InitMessage $message
     */
    public function handle(InitMessage $message)
    {
        $output = $message->getOutput();
        $configStorage = $message->getConfigStorage();

        if ($configStorage->isInitialized($message->getConfig())) {
            $output->writeln(sprintf(
                '%s is already initialised!',
                $message->getCliCommand()->getApplication()->getName()
            ));

            return;
        }

        $result = $configStorage->write($message->getConfig());

        if ($result !== false) {
            $msg = sprintf('Config file created at "<info>%s</info>".', $message->getConfig()->getFileName());
        } else {
            $msg = sprintf(
                '<error>Error: Could not create and write file "<info>%s</info>". '.
                'Please check file and directory permissions.</error>',
                $message->getConfig()->getFileName()
            );
        }
        $output->writeln($msg);
    }
}
