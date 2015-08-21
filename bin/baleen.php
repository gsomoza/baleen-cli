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

/**
 * Attempts to load Composer's autoload.php as either a dependency or a
 * stand-alone package.
 */
$findAutoloader = function () {
    $files = array(
        __DIR__ . '/../../../autoload.php',  // composer dependency
        __DIR__ . '/../vendor/autoload.php', // stand-alone package
    );
    foreach ($files as $file) {
        if (is_file($file)) {
            return require $file;
        }
    }
    return false;
};

if (!$composerAutoloader = $findAutoloader()) {
    if (extension_loaded('phar') && ($uri = Phar::running())) {
        echo 'Looks like the phar has been built without dependencies.' . PHP_EOL . PHP_EOL;
    }
    die(
        'You need to set up the project dependencies using the following commands:' . PHP_EOL .
        'curl -s http://getcomposer.org/installer | php' . PHP_EOL .
        'php composer.phar install' . PHP_EOL
    );
}

use Baleen\Cli\Application;
use Baleen\Cli\Container\ServiceProvider\AppConfigProvider;
use Baleen\Cli\Container\Services;
use League\Container\Container;

$container = new Container();
$container->add(Services::BALEEN_BASE_DIR, dirname(__DIR__));
$container->add(Services::AUTOLOADER, $composerAutoloader);

// the only provider that can't be overwritten
$container->addServiceProvider(new AppConfigProvider());

/** @var \Baleen\Cli\Config\AppConfig $appConfig */
$appConfig = $container->get(Services::CONFIG);

foreach ($appConfig->getProviders() as $name => $class) {
    $provider = new $class();
    $container->addServiceProvider($provider);
}

/** @var Application $app */
$app = $container->get(Services::APPLICATION);
$app->run();
