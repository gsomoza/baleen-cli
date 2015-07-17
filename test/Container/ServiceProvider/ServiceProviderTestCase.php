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

namespace BaleenTest\Baleen\Container\ServiceProvider;

use BaleenTest\Baleen\BaseTestCase;
use League\Container\Container;
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

    protected function setUp()
    {
        parent::setUp();
        $this->instance = null;
        $this->container = m::mock(Container::class)->makePartial();
    }

    /**
     * @param ServiceProvider|m\MockInterface $instance
     */
    protected function setInstance($instance)
    {
        $this->instance = $instance;
        $this->instance->shouldReceive('getContainer')->zeroOrMoreTimes()->andReturn($this->getContainer());
    }


    /**
     * @param $service
     * @param $callback
     * @return m\Mock
     */
    public function assertSingletonProvided($service, $callback)
    {
        return $this->assertServiceProvided($service, 'singleton', $callback);
    }

    /**
     * @param $service
     * @param $type
     * @param $callback
     * @return m\Mock
     */
    private function assertServiceProvided($service, $type, $callback)
    {
        return $this->getContainer()->shouldReceive($type)->with($service, m::type('callable'))->once()
            ->andReturnUsing($callback);
    }

    /**
     * Returns a closure that validates whether the result of a callback passsed as the second argument to the closure
     * is an instance of the specified $instanceOf class.
     *
     * @param $instanceOf
     * @return \Closure
     */
    protected function assertCallbackInstanceOf($instanceOf) {
        $self = $this;
        return function () use ($self, $instanceOf) {
            list(, $callback) = func_get_args();
            $self->assertInstanceOf($instanceOf, $callback());
            return m::mock(Container::class)->makePartial();
        };
    }
}
