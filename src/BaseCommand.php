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

use Baleen\Cli\Command\CommandInterface;
use Baleen\Cli\Container\Services;
use Baleen\Migrations\Exception\InvalidArgumentException;
use League\Container\Container;
use League\Container\ContainerInterface;
use League\Tactician\CommandBus;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AbstractCommand.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class BaseCommand extends Command
{
    /** @var Container */
    protected $container;

    /** @var CommandBus */
    protected $commandBus;

    /** @var string */
    protected $serviceAlias;

    /** @var string */
    protected $serviceClass;

    /**
     * @param ContainerInterface $container
     * @param $serviceAlias
     * @param $serviceClass
     * @throws InvalidArgumentException
     */
    public function __construct(ContainerInterface $container, $serviceAlias, $serviceClass)
    {
        $serviceClass = (string) $serviceClass;
        if (!class_exists($serviceClass) || ! (new $serviceClass) instanceof CommandInterface) {
            throw new InvalidArgumentException(sprintf(
                'Message class "%s" must exist and be an instance of %s',
                $serviceClass,
                CommandInterface::class
            ));
        }
        $this->serviceClass = $serviceClass;

        $serviceAlias = (string) $serviceAlias;
        $this->serviceAlias = $serviceAlias;

        $commandBus = $container->get(Services::COMMAND_BUS);
        if (!$commandBus instanceof CommandBus) {
            throw new InvalidArgumentException(sprintf(
                'Invalid service: expected an instance of "%s".',
                CommandBus::class
            ));
        }
        $this->commandBus = $commandBus;
        $this->container = $container;
        parent::__construct(null); // name will be set inside configure()
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * getCommandBus
     * @return CommandBus
     */
    public function getCommandBus()
    {
        return $this->commandBus;
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var CommandInterface $message */
        $message = $this->getContainer()->get($this->serviceAlias);

        if (get_class($message) !== $this->serviceClass) {
            throw new InvalidArgumentException(sprintf(
                'The specified service alias (%s) and class (%s) do not correspond to each other.',
                $this->serviceAlias,
                $this->serviceClass
            ));
        }

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
