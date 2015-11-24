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

use Interop\Container\ContainerInterface;
use League\Tactician\Exception\MissingHandlerException;
use League\Tactician\Handler\Locator\HandlerLocator;

/**
 * Class MappableContainerLocator
 *
 * Uses a simple map to resolve handler class names and requests those names from a container
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class MappableContainerLocator implements HandlerLocator
{
    /** @var ContainerInterface */
    private $container;

    /** @var array */
    private $map;

    /**
     * MappableContainerLocator constructor.
     *
     * @param ContainerInterface $container
     * @param array $handlerMap
     */
    public function __construct(ContainerInterface $container, array $handlerMap)
    {
        $this->container = $container;
        $this->map = $handlerMap;
    }

    /**
     * Retrieves the handler for a specified command
     *
     * @param string $commandName
     *
     * @return object
     *
     * @throws MissingHandlerException
     */
    public function getHandlerForCommand($commandName)
    {
        if (isset($this->map[$commandName]) || array_key_exists($this->map, $commandName)) {
            $handlerAlias = $this->map[$commandName];
        } else {
            $handlerAlias = $commandName;
        }

        $handler = $this->container->get($handlerAlias);

        // Odds are the callable threw an exception but it always pays to check
        if ($handler === null) {
            throw MissingHandlerException::forCommand($commandName);
        }

        return $handler;
    }
}
