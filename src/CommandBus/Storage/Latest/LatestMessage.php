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

namespace Baleen\Cli\CommandBus\Storage\Latest;

use Baleen\Cli\CommandBus\Migration\AbstractMigrationMessage;
use Symfony\Component\Console\Command\Command;

/**
 * Class Storage\ListMessage
 *
 * Note how this extends from AbstractMigrationMessage. That's because we always need the Migration Repository in order
 * to retrieve the status of each migration.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class LatestMessage extends AbstractMigrationMessage
{
    /**
     * @inheritdoc
     */
    public static function configure(Command $command)
    {
        $command->setName('storage:latest')
            ->setDescription('Outputs the ID of the latest migrated version.');
    }
}
