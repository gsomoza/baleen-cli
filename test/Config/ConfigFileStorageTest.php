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

use Baleen\Cli\Config\AppConfig;
use Baleen\Cli\Config\ConfigStorage;
use BaleenTest\Baleen\BaseTestCase;
use League\Flysystem\FilesystemInterface;
use Mockery as m;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * Class ConfigFileStorageTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class ConfigFileStorageTest extends BaseTestCase
{
    /** @var FilesystemInterface|m\Mock */
    protected $filesystem;

    /** @var ConfigStorage|m\Mock $instance */
    protected $config;

    /** @var ConfigStorage|m\Mock $instance */
    protected $instance;

    /**
     * setUP
     */
    public function setUp()
    {
        parent::setUp();
        $this->filesystem = m::mock(FilesystemInterface::class);
        $this->instance = m::mock(ConfigStorage::class)->makePartial();
        $this->config = m::mock(AppConfig::class);
    }

    /**
     * tearDown
     */
    public function tearDown()
    {
        parent::tearDown();
        $this->filesystem = null;
    }

    /**
     * testConstructor
     */
    public function testConstructor()
    {
        $instance = new ConfigStorage($this->filesystem);
        $this->assertInstanceOf(ConfigStorage::class, $instance);
        $this->assertSame($this->filesystem, $this->getPropVal('projectFileSystem', $instance));
    }

    /**
     * testRead
     * @dataProvider yamlFixtures
     */
    public function testRead($config, $validConfig)
    {
        if (!$validConfig) {
            $this->setExpectedException(InvalidConfigurationException::class);
        }
        $this->doRead($config, null, true);
    }

    /**
     * testReadWithoutFile
     */
    public function testReadWithoutFile()
    {
        $this->doRead('irrelevant', 'doesnt exist', false);
    }

    /**
     * doRead
     *
     * @param $yamlConfig
     * @param null $file
     * @param bool $fileExists
     * @throws \Baleen\Cli\Exception\CliException
     */
    public function doRead($yamlConfig, $file = null, $fileExists = true)
    {
        $expectedFile = $file ?: AppConfig::CONFIG_FILE_NAME;

        $instance = new ConfigStorage($this->filesystem);
        $this->filesystem->shouldReceive('has')->with(m::type('string'))->once()->andReturn($fileExists);

        if ($fileExists) {
            $this->filesystem->shouldReceive('read')->with($expectedFile)->once()->andReturn($yamlConfig);
        } else {
            $this->filesystem->shouldNotReceive('read');
        }

        $result = $instance->read($file);

        $this->assertInstanceOf(AppConfig::class, $result);
    }

    /**
     * Provides sample YAML data
     * @return array
     */
    public function yamlFixtures()
    {
        return [
            ["test: that\nfoo: bar", false],
            ["migrations: { directory: migrations, namespace: Migrations }\nstorage_file: .baleen_versions", true],
        ];
    }

    /**
     * testLoad
     */
    public function testLoad()
    {
        $file = 'some/file';
        $config = 'some config'; // contents don't really matter
        $this->instance->shouldReceive('read')->once()->with($file)->andReturn($config);
        $this->instance->shouldReceive('setAppConfig')->once()->with($config);
        $result = $this->instance->load($file);
        $this->assertSame($result, $config);
    }

    /**
     * testGetSetConfig
     */
    public function testGetSetConfig()
    {
        $instance = new ConfigStorage($this->filesystem);
        $default = $instance->getAppConfig();
        $this->assertInstanceOf(AppConfig::class, $default);

        $instance->setAppConfig($this->config);
        $this->assertSame($this->config, $instance->getAppConfig());
        $this->assertNotSame($this->config, $default);
    }

    /**
     * testIsLoaded
     */
    public function testIsLoaded()
    {
        $instance = new ConfigStorage($this->filesystem);
        $this->assertFalse($instance->isLoaded());

        $instance->setAppConfig($this->config);
        $this->assertTrue($instance->isLoaded());
    }

    /**
     * testGetConfigFileName
     */
    public function testGetConfigFileName()
    {
        $fileName = 'some/file';
        $this->instance->shouldReceive('getAppConfig->getConfigFileName')->once()->andReturn($fileName);

        $result = $this->instance->getConfigFileName();
        $this->assertSame($fileName, $result);
    }

    /**
     * @param $file
     * @param null $defaultFileName
     * @param bool $defaults
     * @dataProvider writeProvider
     */
    public function testWrite($file, $defaultFileName = null, $defaults = true)
    {
        $configToArray = ['foo' => 'bar'];
        $this->config->shouldReceive('toArray')->once()->andReturn($configToArray);

        $expectedFile = $file;
        if (null === $file) {
            $this->config->shouldReceive('getConfigFileName')->once()->andReturn($defaultFileName);
            $expectedFile = $defaultFileName;
        } else {
            $this->config->shouldNotReceive('getConfigFileName');
        }

        $expectedResult = 123; // value doesn't really matter, all we have to do is check that its the same
        $this->filesystem
            ->shouldReceive('write')
            ->once()
            ->with($expectedFile, m::type('string'))
            ->andReturn($expectedResult);

        $this->instance->shouldReceive('isLoaded')->andReturn(true);
        $this->instance->setAppConfig($this->config);
        $this->setPropVal('projectFileSystem', $this->filesystem, $this->instance);

        $result = $this->instance->write($file, $defaults);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function writeProvider()
    {
        return [
            [null, 'some/file'],
            ['another/file'],
            ['another/file', null, false],
        ];
    }

    /**
     * @param $pathOrConfig
     * @param null $fsResult
     * @dataProvider isInitializedProvider
     */
    public function testIsInitialized($pathOrConfig, $fsResult = null)
    {
        $expectedResult = $fsResult;
        $this->setPropVal('projectFileSystem', $this->filesystem, $this->instance);
        if (null !== $fsResult) {
            $this->filesystem->shouldReceive('has')->with(m::type('string'))->once()->andReturn($fsResult);
        } else {
            $expectedResult = false;
            $this->filesystem->shouldNotReceive('has');
        }
        $result = $this->instance->isInitialized($pathOrConfig);
        $this->assertSame($expectedResult, $result);
    }

    public function isInitializedProvider()
    {
        /** @var m\Mock $configMock1 */
        $configMock1 = m::mock(AppConfig::class);
        $configMock1->shouldReceive('getConfigFileName')->andReturn('some/file');
        /** @var m\Mock $configMock2 */
        $configMock2 = m::mock(AppConfig::class);
        $configMock2->shouldReceive('getConfigFileName')->andReturn('some/inexistent/file');
        return [
            [null, null],
            ['some/file', true],
            ['some/file', false],
            [$configMock1, true],
            [$configMock2, false],
        ];
    }
}
