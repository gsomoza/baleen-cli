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
use League\Container\ServiceProvider;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Class ApplicationProvider.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ApplicationProvider extends ServiceProvider
{
    protected $provides = [
        Services::APPLICATION,
        Services::APPLICATION_DISPATCHER,
    ];

    /**
     * Use the register method to register items with the container via the
     * protected $this->container property or the `getContainer` method
     * from the ContainerAwareTrait.
     */
    public function register()
    {
        $container = $this->getContainer();

        $container->singleton(Services::APPLICATION_DISPATCHER, EventDispatcher::class);

        if (!$container->isRegistered(Services::APPLICATION)) {
            $args = [
                Services::COMMANDS,
                Services::HELPERSET,
                Services::APPLICATION_DISPATCHER,
            ];
            $container->singleton(Services::APPLICATION, Application::class)
                ->withArguments($args);
        }

        // register inflectors for the different types of commands
        $container->inflector(RepositoriesAwareInterface::class)
            ->invokeMethod('setRepositories', [Services::REPOSITORY]);

        $container->inflector(StorageAwareInterface::class)
            ->invokeMethod('setStorage', [Services::STORAGE]);

        $container->inflector(TimelineFactoryAwareInterface::class)
            ->invokeMethod('setTimelineFactory', [Services::TIMELINE_FACTORY]);

        $container->inflector(ComparatorAwareInterface::class)
            ->invokeMethod('setComparator', [Services::COMPARATOR]);

        $container->inflector(ConfigStorageAwareInterface::class)
            ->invokeMethod('setConfigStorage', [Services::CONFIG_STORAGE]);

        $container->inflector(AbstractMessage::class)
            ->invokeMethod('setConfig', [Services::CONFIG]);
    }
}
