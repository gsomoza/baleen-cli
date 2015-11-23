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

namespace Baleen\Cli;

use League\Container\Container;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * The entry point to Baleen CLI's commands.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class Application extends ConsoleApplication
{
    /** Version to show in the help / usage message. */
    const VERSION = '0.5.0';

    /**
     * The League\Container instance used by Baleen CLI.
     *
     * @var Container
     */
    protected $container;

    /**
     * @param \Symfony\Component\Console\Command\Command[] $commands Array of Commands available for the Application.
     * @param HelperSet $helperSet HelperSet to be used with the Application.
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(array $commands, HelperSet $helperSet, EventDispatcherInterface $dispatcher = null)
    {
        parent::__construct('Baleen', self::VERSION);
        if (null !== $dispatcher) {
            $this->setDispatcher($dispatcher);
        }
        $this->init($commands, $helperSet);
    }

    /**
     * @param \Symfony\Component\Console\Command\Command[] $commands
     * @param HelperSet                                    $helperSet
     */
    protected function init(array $commands, HelperSet $helperSet)
    {
        $this->setCatchExceptions(true);
        $this->setHelperSet($helperSet);
        $this->addCommands($commands);
    }
}
