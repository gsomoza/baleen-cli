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

namespace BaleenTest\Baleen\Config;

use Baleen\Cli\Config\ConfigurationDefinition;
use BaleenTest\Baleen\BaseTestCase;
use Mockery as m;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class ConfigurationDefinitionTest
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class ConfigurationDefinitionTest extends BaseTestCase
{

    /**
     * testConstructor
     */
    public function testConstructor()
    {
        $instance = new ConfigurationDefinition();
        $this->assertInstanceOf(ConfigurationInterface::class, $instance);
    }

    /**
     * testGetConfigTreeBuilder
     */
    public function testGetConfigTreeBuilder()
    {
        $instance = new ConfigurationDefinition();
        $result = $instance->getConfigTreeBuilder();
        $this->assertInstanceOf(TreeBuilder::class, $result);
        $result->buildTree(); // make sure this works without exceptions
    }
}
