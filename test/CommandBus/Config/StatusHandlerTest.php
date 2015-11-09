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

namespace BaleenTest\Baleen\CommandBus\Config;

use Baleen\Cli\CommandBus\Config\StatusHandler;
use Baleen\Cli\CommandBus\Config\StatusMessage;
use Baleen\Cli\Config\ConfigStorage;
use Baleen\Cli\Helper\VersionFormatter;
use Baleen\Cli\Helper\VersionFormatterInterface;
use Baleen\Migrations\Migration\MigrationInterface;
use Baleen\Migrations\Repository\RepositoryInterface;
use Baleen\Migrations\Storage\StorageInterface;
use Baleen\Migrations\Version as V;
use Baleen\Migrations\Version\Collection;
use Baleen\Migrations\Version\Collection\Linked;
use Baleen\Migrations\Version\Collection\Migrated;
use Baleen\Migrations\Version\Comparator\ComparatorInterface;
use Baleen\Migrations\Version\Comparator\DefaultComparator;
use Baleen\Migrations\Version\VersionInterface;
use BaleenTest\Cli\CommandBus\HandlerTestCase;
use Mockery as m;

/**
 * Class StatusHandlerTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 *
 * @property StatusMessage|m\Mock command
 */
class StatusHandlerTest extends HandlerTestCase
{
    /** @var m\Mock|ConfigStorage */
    protected $configStorage;

    /** @var m\Mock|RepositoryInterface */
    protected $repository;

    /** @var m\Mock|StorageInterface */
    protected $storage;

    /** @var m\Mock|ComparatorInterface */
    protected $comparator;

    /** @var m\Mock */
    protected $comparatorExpectation;

    /**
     * setUp
     */
    public function setUp()
    {
        $this->instance = m::mock(StatusHandler::class)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();
        $this->command = m::mock(StatusMessage::class)->makePartial();
        $this->configStorage = m::mock(ConfigStorage::class);
        $this->command->setConfigStorage($this->configStorage);
        $this->repository = m::mock(RepositoryInterface::class);
        $this->command->setRepository($this->repository);
        $this->storage = m::mock(StorageInterface::class);
        $this->command->setStorage($this->storage);
        $this->command->setComparator(new DefaultComparator());

        parent::setUp();
    }

    /**
     * testHandle
     *
     * @param Linked $available
     * @param Migrated $migrated
     * @param $pendingCount
     *
     * @dataProvider handleProvider
     */
    public function testHandle(Linked $available, Migrated $migrated, $pendingCount)
    {
        $this->repository->shouldReceive('fetchAll')->once()->andReturn($available);
        $this->storage->shouldReceive('fetchAll')->once()->andReturn($migrated);
        $this->command->setRepository($this->repository);
        $this->command->setStorage($this->storage);
        /** @var VersionFormatter|m\Mock $formatter */
        $formatter = m::mock(VersionFormatterInterface::class);
        $this->command
            ->shouldReceive('getCliCommand->getHelper')
            ->with('versionFormatter')
            ->once()
            ->andReturn($formatter);

        $currentMsg = $migrated->last() === false ?
            '/[Nn]othing has been migrated/' :
            '/[Cc]urrent version.*?' . $migrated->last()->getId() . '.*?$/';
        $this->output->shouldReceive('writeln')->once()->with($currentMsg);
        if ($pendingCount > 0) {
            $this->output->shouldReceive('writeln')->with(m::on(function ($messages) use ($pendingCount) {
                return preg_match("/out\\-of\\-date.*?by $pendingCount versions/", $messages[0])
                    && is_string($messages[1])
                    && $messages[2] === '';
            }))->once();
            $this->instance->shouldReceive('printCollection')->with(
                $formatter,
                m::type(Collection::class),
                m::on(function ($messages) {
                    return preg_match('/still pending.*?:$/', $messages[0])
                        && preg_match('/use.*?migrate.*?to migrate them/', $messages[1]);
                }),
                StatusHandler::STYLE_COMMENT
            )->once();
            $this->instance->shouldReceive('printCollection')->with(
                $formatter,
                m::type(Collection::class),
                m::on(function ($messages) {
                    return (bool) preg_match('/[Nn]ew migrations:$/', $messages[0]);
                }),
                StatusHandler::STYLE_INFO
            )->once();
        } else {
            $this->output->shouldReceive('writeln')->with('/up\\-to\\-date/')->once();
        }
        $this->handle();
    }

    /**
     * handleProvider
     * @return array
     */
    public function handleProvider()
    {
        // Calculate combinations of different repository and storage states.
        // All test-cases here should assume sequential execution of migrations, so that we can easily calculate
        // the number of pending migrations with the foreach loop below (see comment below).
        $repVersions = [
            [],
            V::fromArray(range(1,10)),
        ];
        $repositories = [];
        foreach ($repVersions as $versions) {
            $this->linkVersions($versions);
            $repositories[] = new Linked($versions);
        }
        $storageVersions = [
            [],
            V::fromArray(range(1,3)),
        ];
        $storages = [];
        foreach ($storageVersions as $versions) {
            $this->linkVersions($versions, true);
            $storages[] = new Migrated($versions);
        }

        $combinations = $this->combinations([$repositories, $storages]);

        // calculate pending number of migrations and set that as the third parameter. See note in function header.
        foreach($combinations as &$combination) { // NB: addressing by reference!
            $pending = $combination[0]->count() - $combination[1]->count();
            $combination[] = $pending;
        }

        // Additional "special" use-cases below. Pending count (third parameter) should be set manually.

        // Test case for https://github.com/baleen/cli/issues/23
        $repositoryVersions23 = [new V(1), new V(3)];
        $this->linkVersions($repositoryVersions23);
        $storageVersions23 = [new V(1), new V(2)];
        $this->linkVersions($storageVersions23, true);
        $combinations[] = [
            new Linked($repositoryVersions23),
            new Migrated($storageVersions23),
            1 // one migration pending: v3
        ];

        return $combinations;
    }

    /**
     * linkVersions
     * @param $versions
     * @param bool $migrated
     */
    protected function linkVersions(&$versions, $migrated = false)
    {
        foreach ($versions as $v) {
            /** @var VersionInterface $v */
            /** @var MigrationInterface|m\Mock $migration */
            $migration = m::mock(MigrationInterface::class);
            $v->setMigration($migration);
            $v->setMigrated($migrated);
        }
    }

    /**
     * testGetRelativePath
     * @param $from
     * @param $to
     * @param $expected
     * @dataProvider getRelativePathProvider
     */
    public function testGetRelativePath($from, $to, $expected)
    {
        $result = $this->invokeMethod('getRelativePath', $this->instance, [$from, $to]);
        $this->assertEquals($expected, $result);
    }

    /**
     * getRelativePathProvider
     * @return array
     */
    public function getRelativePathProvider()
    {
        $file1 = '/var/log/http/access.log';
        $file2 = '/etc/apache/config.d/httpd.conf';
        $file3 = '/var/log/http/website/error.log';
        $dir1 = '/var';
        return [
            ['', '', ''],                           // empty
            ['/', '/', ''],                         // from root to root
            ['/var', '/var', ''],                   // from directory to directory
            ['/var/log.php', '/var/log.php', ''],   // from file to file
            ['/', '/var/log.php', 'var/log.php'],   // from root to file
            ['', '/var/log.php', 'var/log.php'],    // without $from (same as from root)
            ['/var', '', '..'],                     // towards root
            ['/var', '', '..'],                     // without $to (same as towards root)
            [$file1, $file2, '../../..' . $file2],  // backwards traversal
            [$file1, $file3, 'website/error.log'],  // inward traversal
            [$dir1, $file1, 'log/http/access.log'], // inward traversal from directory
        ];
    }
}
