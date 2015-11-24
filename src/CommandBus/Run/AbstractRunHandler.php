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

namespace Baleen\Cli\CommandBus\Run;

use Baleen\Cli\CommandBus\Util\DomainBusAwareInterface;
use Baleen\Cli\CommandBus\Util\DomainBusAwareTrait;
use Baleen\Cli\CommandBus\Util\PublisherAwareInterface;
use Baleen\Cli\CommandBus\Util\PublisherAwareTrait;
use Baleen\Cli\CommandBus\Util\StorageAwareInterface;
use Baleen\Cli\CommandBus\Util\StorageAwareTrait;
use Baleen\Migrations\Service\DomainBus\Factory\DomainCommandBusFactory;
use Baleen\Migrations\Shared\Event\PublisherInterface;
use Baleen\Migrations\Version\Repository\VersionRepositoryInterface;

/**
 * Class AbstractRunHandler
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class AbstractRunHandler implements DomainBusAwareInterface, StorageAwareInterface, PublisherAwareInterface
{
    use StorageAwareTrait;
    use DomainBusAwareTrait;
    use PublisherAwareTrait;

    /**
     * AbstractRunHandler constructor.
     *
     * @param DomainCommandBusFactory $domainBusFactory
     * @param VersionRepositoryInterface $versionRepository
     * @param PublisherInterface $publisher
     */
    public function __construct(
        DomainCommandBusFactory $domainBusFactory,
        VersionRepositoryInterface $versionRepository,
        PublisherInterface $publisher
    ) {
        $this->setDomainBus($domainBusFactory->createWithInMemoryLocator());
        $this->setStorage($versionRepository);
        $this->setDomainPublisher($publisher);
    }
}
