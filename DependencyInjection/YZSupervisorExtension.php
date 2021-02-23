<?php

namespace YZ\SupervisorBundle\DependencyInjection;

use Supervisor\Supervisor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use YZ\SupervisorBundle\HttpClient\HttpClientFactory;
use YZ\SupervisorBundle\Manager\GroupRestrictedSupervisor;

class YZSupervisorExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('supervisor.servers', $config['servers'][$config['default_environment']]);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $this->configureSupervisorServices($config['servers'][$config['default_environment']], $container);
    }

    private function configureSupervisorServices(array $serversConfig, ContainerBuilder $container): void
    {
        $container->register(HttpClientFactory::class)
            ->setPrivate(true);

        $supervisorList = [];
        foreach ($serversConfig as $name => $config) {
            $clientId = sprintf('supervisor.http_client.%s', $name);
            $container->register($clientId, \fXmlRpc\Client::class)
                ->setFactory([new Reference(HttpClientFactory::class), 'createClient'])
                ->setArguments([$config['host'], $config['port'], $config['username'], $config['password']]);

            $supervisorId = sprintf('supervisor.service.%s', $name);
            $container->register($supervisorId, Supervisor::class)
                ->setArguments([new Reference($clientId)]);

            $groupedSupervisorId = sprintf('supervisor.grouped_supervisor.%s', $name);
            $container->register($groupedSupervisorId, GroupRestrictedSupervisor::class)
                ->setArguments([
                    new Reference($supervisorId),
                    $name,
                    hash('md5', $name),
                    $config['groups'],
                ]);
            
            $supervisorList[] = new Reference($groupedSupervisorId);
        }

        $container->getDefinition('supervisor.manager')
            ->setArguments([$supervisorList]);
    }
}
