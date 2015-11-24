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

namespace Baleen\Cli\CommandBus\Migration\Status;

use Baleen\Migrations\Version\Collection;

/**
 * Class StatusOutputHelper
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
final class StatusOutputHelper
{
    const DB_OUTDATED = 'db.outdated';
    const PENDING_COMMAND = 'migrations.execute';
    const MIGRATIONS_PENDING = 'migrations.pending';
    const HEAD_COMMAND = 'pending.command';
    const MIGRATIONS_NEW = 'migrations.new';
    const DB_UPTODATE = 'db.uptodate';

    /** @var string */
    private $mode;

    /** @var string */
    private $repository = '';

    /** @var string */
    private $binary = '';

    /** @var string[] */
    private $scoped = [
        self::DB_UPTODATE => 'Your database is up-to-date with this repository.',
        self::DB_OUTDATED => 'There are %s versions in this repository that can be migrated.',
        self::PENDING_COMMAND => '  (use "<comment>{{BINARY}} -r {{REPO}}</comment>" to migrate them)',
        self::MIGRATIONS_PENDING => 'Repository migrations still pending:',
        self::HEAD_COMMAND => '  (use "<comment>{{BINARY}} HEAD -r {{REPO}}</comment>" to migrate them)',
        self::MIGRATIONS_NEW => 'New migrations in this repository:',
    ];

    /** @var string[] */
    private $global = [
        self::DB_UPTODATE => 'Your database is up-to-date.',
        self::DB_OUTDATED => 'There are %s versions that can be migrated.',
        self::PENDING_COMMAND => '  (use "<comment>{{BINARY}}</comment>" to run all pending migrations)',
        self::MIGRATIONS_PENDING => 'Old migrations still pending:',
        self::HEAD_COMMAND => '  (use "<comment>{{BINARY}} HEAD</comment>" to migrate them)',
        self::MIGRATIONS_NEW => 'New migrations:',
    ];

    /**
     * StatusOutputHelper constructor.
     *
     * @param string $repository
     */
    public function __construct($repository)
    {
        if (!empty($repository)) {
            $this->mode = 'scoped';
            $this->repository = $repository;
        } else {
            $this->mode = 'global';
        }
        $this->binary = (defined('MIGRATIONS_EXECUTABLE') ? MIGRATIONS_EXECUTABLE . ' ' : '') . 'migrate';
    }

    /**
     * get
     *
     * @param $key
     * @param array $args
     *
     * @return string
     */
    public function get($key, array $args = [])
    {
        $mode = $this->mode;
        if (!isset($this->{$mode}[$key])) {
            return '';
        }

        $str = vsprintf($this->{$mode}[$key], $args);
        return str_replace(['{{REPO}}', '{{BINARY}}'], [$this->repository, $this->binary], $str);
    }
}
