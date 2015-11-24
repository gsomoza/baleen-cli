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

namespace Baleen\Cli\Publisher;

use Baleen\Migrations\Service\Runner\Event\Collection\CollectionBeforeEvent as DomainCollectionBeforeEvent;
use Baleen\Migrations\Service\Runner\Event\Collection\CollectionAfterEvent as DomainCollectionAfterEvent;
use Baleen\Migrations\Service\Runner\Event\Migration\MigrateBeforeEvent as DomainMigrateBeforeEvent;
use Baleen\Migrations\Service\Runner\Event\Migration\MigrateAfterEvent as DomainMigrateAfterEvent;
use Baleen\Migrations\Shared\Event\DomainEventInterface;
use Baleen\Migrations\Shared\Event\PublisherInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Class SymfonyPublisher
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class SymfonyPublisher extends EventDispatcher implements PublisherInterface
{
    /** @var array */
    protected $typeMap = [
        DomainCollectionBeforeEvent::class => CollectionBeforeEvent::class,
        DomainCollectionAfterEvent::class => CollectionAfterEvent::class,
        DomainMigrateBeforeEvent::class => MigrateBeforeEvent::class,
        DomainMigrateAfterEvent::class => MigrateAfterEvent::class,
    ];

    /**
     * Publishes a Domain Event
     *
     * @param DomainEventInterface $domainEvent
     *
     * @return CollectionBeforeEvent
     */
    public function publish(DomainEventInterface $domainEvent)
    {
        $domainClass = get_class($domainEvent);
        if (isset($this->typeMap[$domainClass])) {
            $eventClass = $this->typeMap[$domainClass];
        } else {
            $eventClass = BaseEvent::class;
        }

        $event = new $eventClass($domainEvent);

        return $this->dispatch($eventClass, $event);
    }
}
