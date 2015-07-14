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

use Baleen\Baleen\Command\Storage\StorageCommand;
use Baleen\Baleen\Config\AppConfig;
use Baleen\Baleen\Exception\CliException;
use Baleen\Migrations\Storage\FileStorage;
use League\Container\ServiceProvider;

/**
 * Class StorageProvider
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class StorageProvider extends ServiceProvider
{

    const SERVICE_STORAGE = 'storage';

    protected $provides = [
        self::SERVICE_STORAGE
    ];

    /**
     * @inheritdoc
     */
    public function register()
    {
        $container = $this->getContainer();
        $container->singleton(self::SERVICE_STORAGE, function (AppConfig $config) {
                $storageFile = $config->getStorageFilePath();
                if (!file_exists($storageFile)) {
                    $result = touch($storageFile);
                    if (!$result) {
                        throw new CliException(sprintf(
                            'Could not write storage file "%s".',
                            $config->getStorageFile()
                        ));
                    }
                }
                return new FileStorage($storageFile);
            })
            ->withArgument(AppConfigProvider::SERVICE_CONFIG);
    }
}
