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

namespace Baleen\Cli\Provider;

use Baleen\Cli\Application;
use Baleen\Cli\CommandBus\AbstractMessage;
use Baleen\Cli\CommandBus\Util\ComparatorAwareInterface;
use Baleen\Cli\CommandBus\Util\ConfigStorageAwareInterface;
use Baleen\Cli\CommandBus\Util\RepositoriesAwareInterface;
use Baleen\Cli\CommandBus\Util\StorageAwareInterface;
use Baleen\Cli\CommandBus\Util\TimelineFactoryAwareInterface;
use Baleen\Cli\Config\Config;
use Baleen\Cli\Config\ConfigInterface;
use Baleen\Migrations\Migration\Options\Direction;
use Baleen\Migrations\Version\Comparator\ComparatorInterface;
use Baleen\Migrations\Version\Comparator\MigrationComparator;
use Baleen\Migrations\Version\Comparator\NamespacesAwareComparator;
use League\Container\Argument\RawArgument;
use League\Container\ServiceProvider;
use League\Container\ServiceProvider\AbstractServiceProvider;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Class ApplicationProvider.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ApplicationProvider extends AbstractServiceProvider
{
    protected $provides = [
        Services::APPLICATION,
        Services::APPLICATION_DISPATCHER,
        Services::COMPARATOR,
    ];

    /**
     * Use the register method to register items with the container via the
     * protected $this->container property or the `getContainer` method
     * from the ContainerAwareTrait.
     */
    public function register()
    {
        $container = $this->getContainer();

        $container->share(Services::APPLICATION_DISPATCHER, EventDispatcher::class);

        $container->share(Services::APPLICATION, Application::class)
            ->withArguments([
                Services::COMMANDS,
                Services::HELPERSET,
                Services::APPLICATION_DISPATCHER
            ]);

        $container->share(Services::COMPARATOR, function (Config $config) {
            $repositories = [];
            foreach ($config->getMigrationsConfig() as $repoConfig) {
                $repositories[] = $repoConfig['namespace'];
            }
            return new NamespacesAwareComparator(
                new MigrationComparator(),
                $repositories
            );
        })->withArguments([Services::CONFIG]);
    }
}
