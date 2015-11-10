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

namespace BaleenTest\Cli\Config;

use Baleen\Cli\Config\Config;
use BaleenTest\Cli\BaseTestCase;
use Mockery as m;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * Class ConfigTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class ConfigTest extends BaseTestCase
{
    /**
     * testConstruct
     *
     * @dataProvider constructProvider
     *
     * @param $config
     * @param $useDefaults
     * @param $expected
     */
    public function testConstruct($config, $expected)
    {
        if ($expected === 'ERROR') {
            $this->setExpectedException(InvalidConfigurationException::class);
        }
        $instance = new Config($config);
        $this->assertEquals($expected, $instance->toArray());
    }

    /**
     * constructProvider
     */
    public function constructProvider()
    {
        $validConfig = [
            'providers' => [
                'application' => 'Baleen\Cli\Provider\ApplicationProvider',
                'storage' => 'Baleen\Cli\Provider\StorageProvider',
                'repository' => 'Baleen\Cli\Provider\RepositoryProvider',
                'timeline' => 'Baleen\Cli\Provider\TimelineProvider',
                'helperSet' => 'Baleen\Cli\Provider\HelperSetProvider',
                'commands' => 'Baleen\Cli\Provider\CommandsProvider',
            ],
            'migrations' => [
                [
                    'namespace' => 'Custom',
                    'directory' => 'custom',
                ]
            ],
            'storage' => [
                'file' => '.custom_versions',
            ],
            'plugins' => [
                'Foo' => '\\Bar'
            ],
        ];

        $invalid1 = $validConfig;
        $invalid1['storage'] = '';

        $invalid2 = $validConfig;
        $invalid2['migrations'] = [];

        $invalid3 = $validConfig;
        $invalid3['providers'] = [];

        $invalid4 = $validConfig;
        $invalid4['plugins'] = 'nope';

        return [
            [[], null],
            [$validConfig, $validConfig],
            [$invalid1, 'ERROR'],
            [$invalid2, 'ERROR'],
            [$invalid3, 'ERROR'],
            [$invalid4, 'ERROR']
        ];
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
