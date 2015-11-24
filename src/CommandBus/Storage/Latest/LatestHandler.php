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

namespace Baleen\Cli\CommandBus\Storage\Latest;

use Baleen\Cli\Exception\CliException;
use Baleen\Cli\Helper\VersionFormatter;
use Baleen\Migrations\Version\VersionInterface;

/**
 * Class LatestHandler.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class LatestHandler
{
    /**
     * handle.
     *
     * @param LatestMessage $command
     *
     * @throws CliException
     */
    public function handle(LatestMessage $command)
    {
        $output = $command->getOutput();

        // get all available Versions
        $collection = $command->getRepositories()->fetchAll();

        // filter to a new collection with only versions that have been migrated
        $migrated = $collection->filter(function(VersionInterface $version) {
            return $version->isMigrated();
        });

        if ($migrated->count() === 0) {
            $message = 'No migrated versions found in storage.';
        } else {
            /** @var VersionFormatter $formatter */
            $formatter = $command->getCliCommand()->getHelper('versionFormatter');
            $message = $formatter->formatVersion($migrated->last());
        }
        $output->writeln($message);
    }
}
