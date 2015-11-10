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

namespace Baleen\Cli\Config;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Definition.
 *
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class Definition implements ConfigurationInterface
{
    /**
     * @inheritdoc
     */
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();
        $root = $builder->root('baleen');
        $root->children()
            ->arrayNode('providers')
                ->isRequired()
                ->requiresAtLeastOneElement()
                ->useAttributeAsKey('name')
                ->prototype('scalar')->end()
            ->end()
            ->arrayNode('plugins')
                ->treatNullLike([])
                ->useAttributeAsKey('name')
                ->prototype('scalar')->end()
            ->end()
            ->append($this->addMigrationsNode())
            ->append($this->addStorageNode())
        ->end();

        return $builder;
    }

    /**
     * addMigrationsNode.
     *
     * @return \Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    protected function addMigrationsNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('migrations');

        $node
            ->requiresAtLeastOneElement()
            ->addDefaultChildrenIfNoneSet(0)
            ->prototype('array')
                ->children()
                    ->scalarNode('namespace')
                        ->defaultValue('Migrations')
                    ->end()
                    ->scalarNode('directory')
                        ->defaultValue('migrations')
                    ->end()
                    ->scalarNode('alias')->end()
                ->end()
            ->end();

        return $node;
    }

    /**
     * addStorageNode.
     *
     * @return \Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    protected function addStorageNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('storage');

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('file')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->defaultValue('.baleen_versions')
                ->end()
            ->end()
        ->end();

        return $node;
    }
}
