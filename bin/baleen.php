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

use Baleen\Baleen\Config\ConfigLoader;
use League\Container\Container;

$autoloader = __DIR__ . '/../vendor/autoload.php';

if ( ! file_exists($autoloader)) {
    if (extension_loaded('phar') && ($uri = Phar::running())) {
        echo 'The phar has been builded without the depedencies' . PHP_EOL;
    }
    die('vendor/autoload.php could not be found. Did you run `php composer.phar install`?');
}

require $autoloader;

$container = new Container();
$container->add('config', function() {
    $configFile = getcwd() . '/baleen.yml';
    if (file_exists($configFile)) {
        $config = ConfigLoader::loadFromFile($configFile);
    } else {
        $config = new \Baleen\Baleen\Config\AppConfig([]);
    }
    return $config;
}, true);
$container->addServiceProvider(new \Baleen\Baleen\Container\DefaultServiceProvider());
$container->addServiceProvider(new \Baleen\Baleen\Container\CommandsServiceProvider());

/** @var \Baleen\Baleen\Application $app */
$app = $container->get(\Baleen\Baleen\Application::class);
$app->run();
