<?php

namespace Beefeater\CrudEventBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\DependencyInjection\Definition;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class BeefeaterCrudEventBundle extends AbstractBundle
{
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        if (!$builder->hasDefinition('monolog.logger.crud_event')) {
            $loggerDefinition = new Definition(Logger::class);
            $loggerDefinition->setArguments(['crud_event']);

            $handlerDefinition = new Definition(StreamHandler::class);
            $handlerDefinition->setArguments(['%kernel.logs_dir%/crud_event.log', Logger::DEBUG]);

            $loggerDefinition->addMethodCall('pushHandler', [$handlerDefinition]);
            $builder->setDefinition('monolog.logger.crud_event', $loggerDefinition);
        }

        $container->import('../config/services.yaml');
    }
}
