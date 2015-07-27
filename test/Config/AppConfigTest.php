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
use BaleenTest\Baleen\BaseTestCase;
use Mockery as m;

/**
 * Class AppConfigTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class AppConfigTest extends BaseTestCase
{

    /**
     * testDefaults
     */
    public function testDefaults()
    {
        $conf = new AppConfig();
        $this->assertEquals([
            'migrations'   => [
                'directory' => 'migrations',
                'namespace' => 'Migrations',
            ],
            'storage_file' => AppConfig::VERSIONS_FILE_NAME,
        ], $conf->getDefaults());
    }

    /**
     * testConstruct
     */
    public function testConstruct()
    {
        $customDirectory = 'some-directory';
        $config = ['migrations' => ['directory' => $customDirectory]];
        $instance = new AppConfig($config);
        $this->assertEquals($customDirectory, $instance->getMigrationsDirectory());
    }

    /**
     * testGetMigrationsDirectoryPath
     */
    public function testGetMigrationsDirectoryPath()
    {
        $customDirectory = 'some-directory';
        $config = ['migrations' => ['directory' => $customDirectory]];
        $instance = new AppConfig($config);
        $this->assertContains(DIRECTORY_SEPARATOR . $customDirectory, $instance->getMigrationsDirectoryPath());
    }

    /**
     * testGetMigrationsNamespace
     */
    public function testGetMigrationsNamespace()
    {
        $customNamespace = 'some-namespace';
        $config = ['migrations' => ['namespace' => $customNamespace]];
        $instance = new AppConfig($config);
        $this->assertEquals($customNamespace, $instance->getMigrationsNamespace());
    }

    /**
     * testGetStorageFilePath
     */
    public function testGetStorageFilePath()
    {
        $customStorageFile = 'some-file.txt';
        $config = ['storage_file' => $customStorageFile];
        $instance = new AppConfig($config);
        $this->assertContains(DIRECTORY_SEPARATOR . $customStorageFile, $instance->getStorageFilePath());
    }

    /**
     * testGetConfigFilePath
     */
    public function testGetConfigFilePath()
    {
        $configFileName = AppConfig::CONFIG_FILE_NAME;
        $instance = new AppConfig();
        $this->assertContains($configFileName, $instance->getConfigFilePath());
    }

    public function testWrite()
    {

    }
}
