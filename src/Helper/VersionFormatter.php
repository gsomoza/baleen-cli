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
namespace Baleen\Cli\Helper;

use Baleen\Cli\Util\CalculatesRelativePathsTrait;
use Baleen\Migrations\Version\Collection;
use Baleen\Migrations\Version\VersionInterface;
use Symfony\Component\Console\Helper\Helper;

/**
 * Class VersionFormatter
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
final class VersionFormatter extends Helper implements VersionFormatterInterface
{
    use CalculatesRelativePathsTrait;

    /**
     * Returns the canonical name of this helper.
     *
     * @return string The canonical name
     */
    public function getName()
    {
        return 'versionFormatter';
    }

    /**
     * Formats a single version.
     *
     * @param VersionInterface $version
     * @param $style
     *
     * @return string
     */
    public function formatVersion(VersionInterface $version, $style = 'comment')
    {
        $message = "<$style>{$version->getId()}</$style>";
        $migration = $version->getMigration();
        $fileName = null;
        if ($migration) {
            $class = new \ReflectionClass($migration);
            $fullPath = $class->getFileName();
            $file = $this->getRelativePath(getcwd(), $fullPath);
            $message .= " $file";
        }
        return $message;
    }

    /**
     * @param Collection $versions
     * @param string $style
     *
     * @return array
     */
    public function formatCollection(Collection $versions, $style = 'comment')
    {
        $lines = [];
        foreach ($versions as $version) {
            $lines[] = $this->formatVersion($version, $style);
        }
        return $lines;
    }
}
