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

namespace Baleen\Cli\CommandBus\Run\Migrate;

use Baleen\Cli\CommandBus\Run\AbstractRunHandler;
use Baleen\Cli\Exception\CliException;
use Baleen\Cli\Publisher\SymfonyPublisher;
use Baleen\Migrations\Migration\Options;
use Baleen\Migrations\Service\DomainBus\Migrate\Collection\CollectionCommand;
use Baleen\Migrations\Service\DomainBus\Migrate\Converge\ConvergeCommand;

/**
 * Class MigrateHandler.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class MigrateHandler extends AbstractRunHandler
{
    /**
     * handle.
     *
     * @param MigrateMessage $message
     *
     * @throws CliException
     */
    public function handle(MigrateMessage $message)
    {
        $direction = $message->getDirection();
        $options = (new Options($direction))->withDryRun($message->isDryRun());

        $collection = $message->getRepositories()->fetchAll();
        $targetArg = (string) $message->getInput()->getArgument(MigrateMessage::ARG_TARGET);
        $target = $collection->find($targetArg);
        if (!$target) {
            throw new CliException(sprintf(
                'Migration target with id "%s" could not be found.',
                $targetArg
            ));
        }
        $this->subscribeToEvents($message);

        $this->issueDomainCommand($message, $collection, $target, $options);
    }

    /**
     * Subscribes to the events published by the domain.
     *
     * @param MigrateMessage $message
     *
     * @return void
     */
    protected function subscribeToEvents(MigrateMessage $message)
    {
        /** @var SymfonyPublisher $publisher */
        $publisher = $this->getDomainPublisher();
        $publisher->addSubscriber(new MigrateSubscriber($message));
    }

    /**
     * issueDomainCommand
     *
     * @param MigrateMessage $message
     * @param $collection
     * @param $target
     * @param $options
     *
     * @return void
     * @throws CliException
     */
    protected function issueDomainCommand(MigrateMessage $message, $collection, $target, $options)
    {
        if ($message->getStrategy() === MigrateMessage::STRATEGY_CONVERGE) {
            $command = new ConvergeCommand($collection, $target, $options, $this->getDomainBus(), $this->getStorage());
        } else {
            $command = new CollectionCommand($collection, $target, $options, $this->getStorage());
        }

        $this->getDomainBus()->handle($command);
    }
}
