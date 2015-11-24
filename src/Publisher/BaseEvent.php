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

use Baleen\Migrations\Shared\Event\DomainEventInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class BaseEvent
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class BaseEvent extends Event implements DomainEventInterface
{
    /** @var DomainEventInterface */
    private $domainEvent;

    /**
     * BaseEvent constructor.
     *
     * @param DomainEventInterface $domainEvent
     */
    public function __construct(DomainEventInterface $domainEvent)
    {
        $this->domainEvent = $domainEvent;
    }

    /**
     * __call
     * @param $name
     * @param $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this->domainEvent, $name)) {
            return call_user_func_array([$this->domainEvent, $name], $arguments);
        }
        return null;
    }

    /**
     * @return DomainEventInterface
     */
    public function getDomainEvent()
    {
        return $this->domainEvent;
    }

    /**
     * To help e.g. with deserialization logic
     *
     * @return int
     */
    public function getVersion()
    {
        $this->domainEvent->getVersion();
    }

    /**
     * DateTime for when the event was created
     *
     * @return \DateTime
     */
    public function getOccurredOn()
    {
        $this->domainEvent->getOccurredOn();
    }

    /**
     * Returns the event's payload as an array of key => value objects
     *
     * @return array
     */
    public function getPayload()
    {
        $this->domainEvent->getPayload();
    }
}
