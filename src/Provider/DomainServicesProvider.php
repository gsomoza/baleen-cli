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

namespace Baleen\Cli\Provider;

use Baleen\Cli\Publisher\SymfonyPublisher;
use Baleen\Migrations\Service\Runner\MigrationRunner;
use Baleen\Migrations\Service\Runner\MigrationRunnerInterface;
use Baleen\Migrations\Shared\Event\PublisherInterface;
use League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Class DomainServicesProvider
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class DomainServicesProvider extends AbstractServiceProvider
{
    /** @inheritdoc */
    protected $provides = [
        MigrationRunnerInterface::class,
        PublisherInterface::class
    ];

    /**
     * Use the register method to register items with the container via the
     * protected $this->container property or the `getContainer` method
     * from the ContainerAwareTrait.
     *
     * @return void
     */
    public function register()
    {
        $container = $this->getContainer();

        $container->share(PublisherInterface::class, SymfonyPublisher::class);
        $container->add(MigrationRunnerInterface::class, function() {
            return new MigrationRunner($this->getContainer()->get(Services::PUBLISHER));
        });
    }
}
