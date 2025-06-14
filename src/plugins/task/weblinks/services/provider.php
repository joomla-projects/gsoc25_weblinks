<?php

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\Task\Weblinks\Extension\Weblinks;

return new class () implements ServiceProviderInterface {
    public function register(Container $container): void
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {
                $dispatcher = $container->get(DispatcherInterface::class);
                $plugin = new Weblinks(
                    $dispatcher,
                    (array) PluginHelper::getPlugin('task', 'weblinks')
                );

                $plugin->setDatabase($container->get(DatabaseInterface::class));
                return $plugin;
            }
        );
    }
};
