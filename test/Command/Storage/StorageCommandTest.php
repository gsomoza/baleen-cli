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

namespace BaleenTest\Baleen\Command\Storage;

use Baleen\Cli\Command\Storage\AbstractStorageCommand;
use Baleen\Migrations\Storage\StorageInterface;
use BaleenTest\Baleen\Command\CommandTestCase;
use Mockery as m;

/**
 * Class StorageCommandTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class StorageCommandTest extends CommandTestCase
{

    public function testSetGetStorage()
    {
        $storage = m::mock(StorageInterface::class);
        /** @var AbstractStorageCommand|m\Mock $instance */
        $instance = m::mock(AbstractStorageCommand::class)->makePartial();
        $instance->setStorage($storage);
        $this->assertSame($storage, $instance->getStorage());
    }
}
