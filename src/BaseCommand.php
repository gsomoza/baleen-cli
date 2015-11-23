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

use Baleen\Cli\CommandBus\MessageInterface;
use Baleen\Cli\Helper\ContainerHelper;
use Baleen\Cli\Provider\Services;
use Baleen\Migrations\Exception\InvalidArgumentException;
use League\Container\Container;
use League\Container\ContainerInterface;
use League\Tactician\CommandBus;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The base Command class used to build all the command definitions for the Application.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class BaseCommand extends Command
{
    /**
     * A reference to the CommandBus in charge of handling Messages.
     *
     * @var CommandBus
     */
    protected $commandBus;

    /** @var string */
    protected $serviceAlias;

    /** @var string */
    protected $serviceClass;

    /**
     * @param CommandBus $commandBus
     * @param string $serviceAlias The key in the Container for the command that the instance of this class represents.
     *
     * @param string $serviceClass Needed in order to run certain checks against the class before instantiating it
     *                             with the container. This helps us make those checks without triggering all the other
     *                             services through the Container's DI functionality.
     *
     * @throws InvalidArgumentException
     *
     */
    public function __construct(CommandBus $commandBus, $serviceClass)
    {
        if (!class_exists($serviceClass)) {
            throw new InvalidArgumentException(sprintf(
                'Message class "%s" must exist and be an instance of %s',
                $serviceClass,
                MessageInterface::class
            ));
        }
        $this->serviceClass = $serviceClass;

        $this->commandBus = $commandBus;
        parent::__construct(null); // name will be set inside configure()
    }

    /**
     * @return \Interop\Container\ContainerInterface
     */
    public function getContainer()
    {
        /** @var ContainerHelper $helper */
        $helper = $this->getHelper('container');
        return $helper->getContainer();
    }

    /**
     * getCommandBus.
     *
     * @return CommandBus
     */
    public function getCommandBus()
    {
        return $this->commandBus;
    }

    /**
     * Executes the current command by retrieving its associated Message from the Container, setting the Input and
     * Output according to what was received as parameters, and finally passing that Message to the CommandBus for
     * handling.
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return int|null null or 0 if everything went fine, or an error code
     *
     * @throws InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var MessageInterface $message */
        $message = $this->getContainer()->get($this->serviceClass);

        $message->setCliCommand($this);
        $message->setInput($input);
        $message->setOutput($output);
        $this->getCommandBus()->handle($message);
    }

    /**
     * Calls the message's static "configure" public function passing $this as argument to allow the message to
     * configure the command.
     */
    protected function configure()
    {
        $callable = [$this->serviceClass, 'configure'];
        forward_static_call($callable, $this);
    }
}
