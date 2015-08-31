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

namespace Baleen\Cli\Container\ServiceProvider;

use Baleen\Cli\Application;
use Baleen\Cli\Command\AbstractCommand;
use Baleen\Cli\Command\Util\ComparatorAwareInterface;
use Baleen\Cli\Command\Util\ConfigStorageAwareInterface;
use Baleen\Cli\Command\Util\RepositoryAwareInterface;
use Baleen\Cli\Command\Util\StorageAwareInterface;
use Baleen\Cli\Command\Util\TimelineAwareInterface;
use Baleen\Cli\Container\Services;
use League\Container\ServiceProvider;

/**
 * Class DefaultProvider.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DefaultProvider extends ServiceProvider
{
    protected $provides = [
        Services::APPLICATION,
    ];

    /**
     * Use the register method to register items with the container via the
     * protected $this->container property or the `getContainer` method
     * from the ContainerAwareTrait.
     */
    public function register()
    {
        $container = $this->getContainer();

        if (!$container->isRegistered(Services::APPLICATION)) {
            $container->singleton(Services::APPLICATION, Application::class)
                ->withArguments([
                    Services::COMMANDS,
                    Services::HELPERSET,
                ]);
        }

        // register inflectors for the different types of commands
        $container->inflector(RepositoryAwareInterface::class)
            ->invokeMethods([
                'setRepository' => [Services::REPOSITORY],
                'setFilesystem' => [Services::REPOSITORY_FILESYSTEM],
            ]);

        $container->inflector(StorageAwareInterface::class)
            ->invokeMethod('setStorage', [Services::STORAGE]);

        $container->inflector(TimelineAwareInterface::class)
            ->invokeMethod('setTimeline', [Services::TIMELINE]);

        $container->inflector(ComparatorAwareInterface::class)
            ->invokeMethod('setComparator', [Services::TIMELINE_COMPARATOR]);

        $container->inflector(ConfigStorageAwareInterface::class)
            ->invokeMethod('setConfigStorage', [Services::CONFIG_STORAGE]);

        $container->inflector(AbstractCommand::class)
            ->invokeMethod('setConfig', [Services::CONFIG]);
    }
}
