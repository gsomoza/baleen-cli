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

use Baleen\Cli\Helper\ConfigHelper;
use Baleen\Cli\Provider\Services;
use Mockery as m;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;

/**
 * Class HelperSetProviderTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class HelperSetProviderTest extends ServiceProviderTestCase
{

    public function testRegister()
    {
        $self = $this;
        $container = $this->container;
        $this->container->shouldReceive('withArgument')->with(Services::CONFIG)->once();
        $this->container->shouldReceive('add')->with(Services::HELPERSET_QUESTION, QuestionHelper::class)->once();

        $this->setInstance(m::mock(\Baleen\Cli\Provider\HelperSetProvider::class)->makePartial());

        $this->container->shouldReceive('get')->with(Services::HELPERSET_QUESTION)
            ->andReturn(new QuestionHelper());

        $this->assertSingletonProvided(
            Services::HELPERSET,
            function() use ($self, $container) {
                list(, $callback) = func_get_args();
                $self->assertInstanceOf(HelperSet::class, $callback());
                return $container;
            }
        );
        $this->instance->register();
    }
}
