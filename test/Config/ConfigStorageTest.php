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
use Baleen\Cli\Config\ConfigStorage;
use BaleenTest\Cli\BaseTestCase;
use League\Flysystem\FilesystemInterface;
use Mockery as m;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * Class ConfigStorageTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class ConfigStorageTest extends BaseTestCase
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
        $this->config = m::mock(Config::class);
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
        $instance = new ConfigStorage(Config::class, $this->filesystem);
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
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testReadWithoutFile()
    {
        $this->doRead('irrelevant', 'doesnt exist', false);
    }

    /**
     * doRead
     *
     * @param $yamlConfig
     * @param null|string $file
     * @param bool $fileExists
     * @throws \Baleen\Cli\Exception\CliException
     */
    public function doRead($yamlConfig, $file = null, $fileExists = true)
    {
        $expectedFile = $file ?: Config::CONFIG_FILE_NAME;

        $instance = new ConfigStorage(Config::class, $this->filesystem);
        $this->filesystem->shouldReceive('has')->with(m::type('string'))->once()->andReturn($fileExists);

        if ($fileExists) {
            $this->filesystem->shouldReceive('read')->with($expectedFile)->once()->andReturn($yamlConfig);
        } else {
            $this->filesystem->shouldNotReceive('read');
        }

        $result = $instance->load($file);

        $this->assertInstanceOf(Config::class, $result);
    }

    /**
     * Provides sample YAML data
     * @return array
     */
    public function yamlFixtures()
    {
        return [
            [
                "test: that" . PHP_EOL .
                "foo: bar",
                false
            ],
            [
                'providers: { foo: bar }' . PHP_EOL .
                'migrations:' . PHP_EOL .
                '  - { namespace: Migrations, directory: migrations }' . PHP_EOL .
                'storage: { file: .baleen_versions }'
                , true
            ],
        ];
    }

    /**
     * @param $file
     * @dataProvider writeProvider
     */
    public function testWrite($file)
    {
        $configToArray = ['foo' => 'bar'];
        $this->config->shouldReceive('getCleanArray')->once()->andReturn($configToArray);


        $expectedFile = null === $file ? Config::CONFIG_FILE_NAME : $file;
        $this->config->shouldReceive('getFileName')->once()->andReturn($file);

        $expectedResult = 123; // value doesn't really matter, all we have to do is check that its the same
        $this->filesystem
            ->shouldReceive('write')
            ->once()
            ->with($expectedFile, m::type('string'))
            ->andReturn($expectedResult);

        $this->setPropVal('projectFileSystem', $this->filesystem, $this->instance);

        $result = $this->instance->write($this->config);

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
        $configMock1 = m::mock(Config::class);
        $configMock1->shouldReceive('getFileName')->andReturn('some/file');
        /** @var m\Mock $configMock2 */
        $configMock2 = m::mock(Config::class);
        $configMock2->shouldReceive('getFileName')->andReturn('some/inexistent/file');
        return [
            [$configMock1, true],
            [$configMock2, false],
        ];
    }
}
