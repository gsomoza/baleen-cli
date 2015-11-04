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

namespace BaleenTest\Cli\Provider;

use Baleen\Cli\Config\Config;
use BaleenTest\Cli\BaseTestCase;
use League\Container\Container;
use League\Container\Definition\Factory;
use League\Container\ServiceProvider;
use Mockery as m;

/**
 * Class ServiceProviderTestCase
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class ServiceProviderTestCase extends BaseTestCase
{

    /** @var ServiceProvider|m\Mock */
    protected $instance;

    /** @var m\Mock */
    protected $config;

    /**
     * @return ServiceProvider|m\Mock
     */
    public function getInstance()
    {
        return $this->instance;
    }

    /** @var ServiceProvider|m\Mock */
    protected $container;

    /**
     * @return ServiceProvider|m\Mock
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param ServiceProvider|m\Mock $container
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }

    public function setUp()
    {
        parent::setUp();
        $this->container = m::mock(Container::class)->makePartial();
        $prop = new \ReflectionProperty($this->container, 'factory');
        $prop->setAccessible(true);
        $prop->setValue($this->container, new Factory());

        $this->config = m::mock(Config::class);
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->instance = null;
        $this->container = null;
    }

    /**
     * @param m\Mock $instance
     */
    protected function setInstance($instance)
    {
        $this->instance = $instance;
        $this->instance->shouldReceive('getContainer')->zeroOrMoreTimes()->andReturn($this->getContainer());
    }


    /**
     * @param $service
     * @param \Closure $callback
     * @param string $concreteType
     * @return m\Mock
     */
    public function assertSingletonProvided($service, $callback, $concreteType = 'callable')
    {
        $this->assertServiceProvided($service, 'singleton', $callback, $concreteType);
        return $this->getContainer();
    }

    /**
     * @param $service
     * @param string $type
     * @param \Closure $callback
     * @param string $concreteType
     * @return m\Mock
     */
    protected function assertServiceProvided($service, $type, $callback, $concreteType = 'callable')
    {
        $this->getContainer()->shouldReceive($type)->with($service, m::type($concreteType))->once()
            ->andReturnUsing($callback);
        return $this->getContainer();
    }

    /**
     * Returns a closure that validates whether the result of a service factory callback is an instance of the
     * specified $instanceOf class.
     *
     * @param $instanceOf
     * @param array $factoryArgs
     * @return \Closure
     */
    protected function assertCallbackInstanceOf($instanceOf, $factoryArgs = [], \Closure $additionalAssertions = null) {
        if ($factoryArgs && !is_array($factoryArgs)) {
            $factoryArgs = [$factoryArgs];
        }
        return $this->assertableCallback(
            function($factory) use ($instanceOf, $factoryArgs, $additionalAssertions) {
                if (is_string($factory)) {
                    // simulate the Container's behaviour when a string is supplied as a concrete
                    $factory = function () use ($factory) {
                        $refl = new \ReflectionClass($factory);
                        return $refl->newInstanceArgs(func_get_args());
                    };
                }
                if (is_object($factory) && $factory instanceof \Closure) {
                    $factory = $factory->bindTo($this);
                }
                $func = [$factory, '__invoke'];
                $result = call_user_func_array($func, $factoryArgs);
                $this->assertInstanceOf($instanceOf, $result);
                if (null !== $additionalAssertions) {
                    $additionalAssertions = $additionalAssertions->bindTo($this);
                    $additionalAssertions->__invoke($result);
                }
                return $this->getContainer();
            }
        );
    }

    /**
     * Returns a closure that calls $callback with scope $this (this test case object), passing the service $factory
     * as the first argument.
     *
     * @param \Closure $callback
     * @return \Closure
     */
    protected function assertableCallback($callback)
    {
        $self = $this;
        return function() use ($self, $callback) {
            /** @var \Closure $callback */
            list(, $factory) = func_get_args();
            $callback = $callback->bindTo($self, $factory);
            return $callback->__invoke($factory);
        };
    }

    /**
     * @param $name
     */
    protected function assertRegistersProvider($name)
    {
        $this->getContainer()->shouldReceive('addServiceProvider')->with($name)->once();
    }

    /**
     * @param $name
     * @param array $withMethods
     */
    protected function assertRegistersInflector($name, $withMethods = [])
    {
        $definitionMock = m::mock();
        if (count($withMethods) == 1) {
            $methodName = 'invokeMethod';
            $withMethods = [key($withMethods), current($withMethods)];
        } else {
            $methodName = 'invokeMethods';
            $withMethods = [$withMethods];
        }
        $exp = $definitionMock->shouldReceive($methodName)->once();
        $exp = call_user_func_array([$exp, 'with'], $withMethods);
        $exp->andReturn($definitionMock);

        $this->getContainer()->shouldReceive('inflector')->with($name)->once()->andReturn($definitionMock);
    }
}
