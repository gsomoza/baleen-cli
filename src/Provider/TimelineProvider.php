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

use Baleen\Cli\Config\Config;
use Baleen\Migrations\Timeline\TimelineFactory;
use Baleen\Migrations\Version\Collection\Resolver\DefaultResolverStackFactory;
use Baleen\Migrations\Version\Comparator\MigrationComparator;
use Baleen\Migrations\Version\Comparator\NamespacesAwareComparator;
use League\Container\ServiceProvider;

/**
 * Class TimelineProvider.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class TimelineProvider extends ServiceProvider
{
    /**
     * @inheritdoc
     */
    protected $provides = [
        Services::TIMELINE_FACTORY,
        Services::RESOLVER,
        Services::COMPARATOR,
    ];

    /**
     * @inheritdoc
     */
    public function register()
    {
        $container = $this->getContainer();

        if (!$container->isRegistered(Services::COMPARATOR)) {
            $container->singleton(Services::COMPARATOR, function(Config $config) {
                $repositories = [];
                foreach ($config->getMigrationsConfig() as $repoConfig) {
                    $repositories[] = $repoConfig['namespace'];
                }
                return new NamespacesAwareComparator(
                    NamespacesAwareComparator::ORDER_NORMAL,
                    new MigrationComparator(),
                    $repositories
                );
            })->withArgument(Services::CONFIG);
        }

        if (!$container->isRegistered(Services::RESOLVER)) {
            $container->add(Services::RESOLVER, function() {
                $factory = new DefaultResolverStackFactory();
                return $factory->create();
            });
        }

        $container->singleton(Services::TIMELINE_FACTORY, TimelineFactory::class)->withArguments([
            Services::RESOLVER,
            Services::COMPARATOR,
        ]);
    }
}
