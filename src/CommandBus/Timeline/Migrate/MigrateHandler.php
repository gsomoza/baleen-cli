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

namespace Baleen\Cli\CommandBus\Timeline\Migrate;

use Baleen\Cli\Exception\CliException;
use Baleen\Migrations\Migration\Options;
use Baleen\Migrations\Migration\Options\Direction;

/**
 * Class MigrateHandler.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class MigrateHandler
{
    /** @var MigrateMessage */
    private $command;

    /**
     * handle.
     *
     * @param MigrateMessage $message
     *
     * @throws CliException
     */
    public function handle(MigrateMessage $message)
    {
        $this->command = $message;

        $options = (new Options())->withDryRun($message->isDryRun());

        $timeline = $message->getTimeline();

        $timeline->getEventDispatcher()->addSubscriber(
            new MigrateSubscriber($message)
        );

        $strategy = $message->getStrategy();
        $target = $message->getTarget();

        $timeline->$strategy($target, $options);
    }
}
