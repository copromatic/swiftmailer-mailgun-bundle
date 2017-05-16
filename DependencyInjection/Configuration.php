<?php

namespace cspoo\Swiftmailer\MailgunBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('cspoo_swiftmailer_mailgun');

        $this->addAPIConfigSection($rootNode);

        return $treeBuilder;
    }

    private function addAPIConfigSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->beforeNormalization()
            ->ifTrue(function ($v) { return is_array($v) && !array_key_exists('transports', $v) && !array_key_exists('mailer', $v); })
            ->then(function ($v) {
                $transport = array();
                foreach ($v as $key => $value) {
                    if ('default_transport' == $key) {
                        continue;
                    }
                    $transport[$key] = $v[$key];
                    unset($v[$key]);
                }
                $v['default_transport'] = isset($v['default_transport']) ? (string) $v['default_transport'] : 'default';
                $v['transports'] = array($v['default_transport'] => $transport);

                return $v;
            })
            ->end()
            ->children()
                ->scalarNode('key')->end()
                ->scalarNode('domain')->end()
                ->scalarNode('default_transport')->end()
                ->scalarNode('http_client')->end()
                ->append($this->getTransportsNode())
            ->end()
        ;
    }

    private function getTransportsNode() {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('transports');

        $node
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('name')
            ->prototype('array')
            ->children()
                ->scalarNode('key')->isRequired()->end()
                ->scalarNode('domain')->isRequired()->end()
            ->end()
        ;
        return $node;
    }
}
