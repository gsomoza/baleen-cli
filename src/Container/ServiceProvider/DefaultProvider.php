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

namespace Baleen\Baleen\Container\ServiceProvider;

use Baleen\Baleen\Application;
use Baleen\Baleen\Command\AbstractCommand;
use Baleen\Baleen\Command\Repository\RepositoryCommand;
use Baleen\Baleen\Command\Storage\StorageCommand;
use Baleen\Migrations\Version\Comparator\DefaultComparator;
use League\Container\ServiceProvider;

/**
 * Class DefaultProvider
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class DefaultProvider extends ServiceProvider
{

    const SERVICE_AUTOLOADER = 'composerAutoloader';

    protected $provides = [
        Application::class,
        DefaultComparator::class,
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

        if ($container->isRegistered(Application::class)) {
            return; // only needs to be executed once
        }

        $container->addServiceProvider(new AppConfigProvider);
        $container->addServiceProvider(new StorageProvider);
        $container->addServiceProvider(new RepositoryProvider);
        $container->addServiceProvider(new HelperSetProvider);
        $container->addServiceProvider(new CommandsProvider);

        $container->inflector(AbstractCommand::class)
            ->invokeMethod('setComparator', [DefaultComparator::class])
            ->invokeMethod('setConfig', [AppConfigProvider::SERVICE_CONFIG]);

        $container->inflector(RepositoryCommand::class)
            ->invokeMethod('setRepository', [RepositoryProvider::SERVICE_REPOSITORY]);

        $container->inflector(StorageCommand::class)
            ->invokeMethod('setStorage', [StorageProvider::SERVICE_STORAGE]);

        $container->singleton(Application::class, null, true)
            ->withArguments([
                CommandsProvider::SERVICE_COMMANDS,
                HelperSetProvider::SERVICE_HELPERSET,
            ]);

        $container->singleton(DefaultComparator::class);
    }
}
