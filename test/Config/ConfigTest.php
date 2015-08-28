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

namespace BaleenTest\Baleen\Config;

use Baleen\Cli\Config\Config;
use Baleen\Cli\Container\ServiceProvider\CommandsProvider;
use Baleen\Cli\Container\ServiceProvider\DefaultProvider;
use Baleen\Cli\Container\ServiceProvider\HelperSetProvider;
use Baleen\Cli\Container\ServiceProvider\RepositoryProvider;
use Baleen\Cli\Container\ServiceProvider\StorageProvider;
use Baleen\Cli\Container\ServiceProvider\TimelineProvider;
use BaleenTest\Baleen\BaseTestCase;
use Mockery as m;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * Class ConfigTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class ConfigTest extends BaseTestCase
{

    /**
     * testDefaults
     */
    public function testDefaults()
    {
        $conf = new Config();
        $expected = [
            'providers' => [
                'application' => DefaultProvider::class,
                'storage' => StorageProvider::class,
                'repository' => RepositoryProvider::class,
                'timeline' => TimelineProvider::class,
                'helperSet' => HelperSetProvider::class,
                'commands' => CommandsProvider::class,
            ],
            'migrations' => [
                'directory' => 'migrations',
                'namespace' => 'Migrations',
            ],
            'storage' => [
                'file' => Config::VERSIONS_FILE_NAME
            ],
        ];
        $this->assertEquals($expected, $conf->getDefaults());
        $this->assertEquals($expected, $conf->toArray());
    }

    /**
     * testConstruct
     * @dataProvider constructProvider
     */
    public function testConstruct($config, $useDefaults, $expected)
    {
        if ($expected === 'ERROR') {
            $this->setExpectedException(InvalidConfigurationException::class);
        }
        $instance = new Config($config, $useDefaults);
        $this->assertEquals($expected, $instance->toArray());
    }

    /**
     * constructProvider
     */
    public function constructProvider()
    {
        $defaultConfig = [
            'providers' => [
                'application' => 'Baleen\Cli\Container\ServiceProvider\DefaultProvider',
                'storage' => 'Baleen\Cli\Container\ServiceProvider\StorageProvider',
                'repository' => 'Baleen\Cli\Container\ServiceProvider\RepositoryProvider',
                'timeline' => 'Baleen\Cli\Container\ServiceProvider\TimelineProvider',
                'helperSet' => 'Baleen\Cli\Container\ServiceProvider\HelperSetProvider',
                'commands' => 'Baleen\Cli\Container\ServiceProvider\CommandsProvider',
            ],
            'migrations' => [
                'directory' => 'migrations',
                'namespace' => 'Migrations',
            ],
            'storage' => [
                'file' => '.baleen_versions',
            ],
        ];
        $resultSet0 = $defaultConfig;
        $resultSet0['migrations']['directory'] = 'custom-directory';
        return [
            [
                [],
                true,
                $defaultConfig,
            ],
            [
                ['migrations' => ['directory' => 'custom-directory',],],
                true,
                $resultSet0,
            ],
            [
                [],
                false,
                'ERROR'
            ]
        ];
    }

    /**
     * testGetMigrationsDirectoryPath
     */
    public function testGetMigrationsDirectoryPath()
    {
        $customDirectory = 'some-directory';
        $config = ['migrations' => ['directory' => $customDirectory]];
        $instance = new Config($config);
        $this->assertContains(DIRECTORY_SEPARATOR . $customDirectory, $instance->getMigrationsDirectoryPath());
    }

    /**
     * testGetMigrationsNamespace
     */
    public function testGetMigrationsNamespace()
    {
        $customNamespace = 'some-namespace';
        $config = ['migrations' => ['namespace' => $customNamespace]];
        $instance = new Config($config);
        $this->assertEquals($customNamespace, $instance->getMigrationsNamespace());
    }

    /**
     * testGetStorageFilePath
     */
    public function testGetStorageFilePath()
    {
        $customStorageFile = 'some-file.txt';
        $config = ['storage' => ['file' => $customStorageFile]];
        $instance = new Config();
        $this->setPropVal('config', $config, $instance);
        $this->assertContains(DIRECTORY_SEPARATOR . $customStorageFile, $instance->getStorageFilePath());
    }

    /**
     * testGetConfigFilePath
     */
    public function testGetConfigFilePath()
    {
        $configFileName = Config::CONFIG_FILE_NAME;
        $instance = new Config();
        $this->assertContains($configFileName, $instance->getConfigFilePath());
    }

    /**
     * testGetProviders
     */
    public function testGetProviders()
    {
        $customProviders = ['foo' => 'bar'];
        $config = ['providers' => $customProviders];
        $instance = new Config();
        $this->setPropVal('config', $config, $instance);
        $this->assertEquals($customProviders, $instance->getProviders());
    }

    /**
     * testGetCleanArray
     */
    public function testGetCleanArray()
    {
        $config = ['providers' => ['should_be' => 'removed'], 'should' => 'remain'];
        $instance = new Config();
        $this->setPropVal('config', $config, $instance);
        $this->assertArrayNotHasKey('providers', $instance->getCleanArray());
    }
}
