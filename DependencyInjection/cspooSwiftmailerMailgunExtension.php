<?php

namespace cspoo\Swiftmailer\MailgunBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class cspooSwiftmailerMailgunExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $definitionDecorator = new DefinitionDecorator('swiftmailer.transport.eventdispatcher.abstract');
        $container->setDefinition('mailgun.swift_transport.eventdispatcher', $definitionDecorator);

        foreach ($config['transports'] as $key => $transport) {
            // Mailgun php lib
            $container->setDefinition('mailgun.library.'.$key,
                new Definition('%mailgun.class%', [
                    $transport['key'],
                    null
                ])
            );
            // Transport Swiftmailer
            $container->setDefinition('mailgun.swift_transport.transport.'.$key,
                new Definition('%mailgun.swift_transport.transport.class%', [
                    new Reference('mailgun.swift_transport.eventdispatcher'),
                    new Reference('mailgun.library.'.$key),
                    $transport['domain']
                ])
            );
            if (!empty($config['http_client'])) {
                $container->getDefinition('mailgun.library.'.$key)->replaceArgument(1,
                    new Reference($config['http_client'])
                );
            }

            // Set up aliases
            $container->setAlias('mailgun.'.$key, 'mailgun.swift_transport.transport.'.$key);
            if ($key == $config['default_transport']) {
                $container->setAlias('mailgun', 'mailgun.swift_transport.transport.'.$key);
                $container->setAlias('swiftmailer.mailer.transport.mailgun', 'mailgun.swift_transport.transport.'.$key);
            }
        }
    }
}
