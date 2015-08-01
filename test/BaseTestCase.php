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

namespace BaleenTest\Baleen;

use Mockery as m;

/**
 * Class BaseTestCase
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class BaseTestCase extends \PHPUnit_Framework_TestCase
{

    /**
     * tearDown - clean up Mockery
     */
    public function tearDown()
    {
        m::close();
    }

    /**
     * @param $propName
     * @param $instance
     * @return mixed
     */
    public function getPropVal($propName, $instance)
    {
        $prop = new \ReflectionProperty($instance, $propName);
        $prop->setAccessible(true);
        return $prop->getValue($instance);
    }

    /**
     * @param $propName
     * @param $value
     * @param $instance
     */
    public function setPropVal($propName, $value, $instance)
    {
        $prop = new \ReflectionProperty($instance, $propName);
        $prop->setAccessible(true);
        $prop->setValue($instance, $value);
    }

    /**
     * @param $methodName
     * @param $instance
     * @param $args
     * @return mixed
     */
    public function invokeMethod($methodName, $instance, $args)
    {
        $method = new \ReflectionMethod($instance, $methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($instance, $args);
    }
}
