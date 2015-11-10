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

namespace Baleen\Cli\CommandBus;

use Baleen\Migrations\Version\Collection;
use Baleen\Migrations\Version\VersionInterface;

/**
 * Class AbstractHelper
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
abstract class AbstractHelper
{
    /** @var MessageInterface */
    private $message;

    /**
     * StatusHelper constructor.
     *
     * @param MessageInterface $message
     */
    final public function __construct(MessageInterface $message)
    {
        $this->message = $message;
    }

    /**
     * @return MessageInterface
     */
    final protected function getMessage()
    {
        return $this->message;
    }

    /**
     * Format a version using the VersionFormatter helper
     *
     * @param VersionInterface $version
     * @param string $style
     *
     * @return string
     */
    final public function formatVersion(VersionInterface $version, $style = 'comment')
    {
        $formatter = $this->getVersionFormatter();
        return $formatter->formatVersion($version, $style);
    }

    /**
     * Format a collection of versions using the VersionFormatter helper
     *
     * @param Collection $collection
     * @param string $style
     *
     * @return string
     */
    final public function formatCollection(Collection $collection, $style = 'comment')
    {
        $formatter = $this->getVersionFormatter();
        return $formatter->formatCollection($collection, $style);
    }

    /**
     * getVersionFormatter
     *
     * @return \Baleen\Cli\Helper\VersionFormatter
     */
    final public function getVersionFormatter()
    {
        return $this->getMessage()->getCliCommand()->getHelper('versionFormatter');
    }
}
