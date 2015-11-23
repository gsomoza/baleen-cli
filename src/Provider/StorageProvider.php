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

use Baleen\Cli\Config\Config;
use Baleen\Migrations\Version\Repository\VersionRepository;
use Baleen\Storage\FlyStorage;
use Baleen\Storage\FlyVersionMapper;
use League\Container\ServiceProvider;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

/**
 * Class StorageProvider.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class StorageProvider extends AbstractServiceProvider
{
    protected $provides = [
        Services::VERSION_REPOSITORY,
    ];

    /**
     * @inheritdoc
     */
    public function register()
    {
        $container = $this->getContainer();
        $container->share(Services::VERSION_REPOSITORY, function (Config $config) {
            $adapter = new Local(getcwd());
            $filesystem = new Filesystem($adapter);
            $fileName = $config->getStorageFile();
            $mapper = new FlyVersionMapper($filesystem, $fileName);
            return new VersionRepository($mapper);
        })->withArgument(Services::CONFIG);
    }
}
