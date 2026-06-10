<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2015 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisDemoCommerce;

use MelisDemoCommerce\Listener\SiteCommerceProductPriceRangePluginListener;
use MelisDemoCommerce\Listener\SiteCommerceRelatedProductsPluginListener;
use Laminas\Mvc\ModuleRouteListener;
use Laminas\Mvc\MvcEvent;
use Laminas\ModuleManager\ModuleManager;
use Laminas\Stdlib\ArrayUtils;
use MelisDemoCommerce\Listener\SiteMenuCustomizationListener;
use MelisDemoCommerce\Listener\SiteBreadcrumbCustomizationListener;
// use MelisDemoCommerce\Listener\SiteFakePaymentListener;
// use MelisDemoCommerce\Listener\SiteFakePaymentProcesstListener;
use MelisDemoCommerce\Listener\SiteOrderReturnProductPluginListener;
use MelisDemoCommerce\Listener\SiteShipmentCostListener;
use MelisDemoCommerce\Listener\SiteFakePaypalStyleListener;
use MelisDemoCommerce\Listener\SiteFakePaypalStyleProcesstListener;
use MelisDemoCommerce\Listener\SiteCommerceCategoryProductListPluginListener;
use MelisDemoCommerce\Listener\SiteProductShowPluginListener;
use MelisDemoCommerce\Listener\SiteCommerceProductListPluginListener;
use MelisDemoCommerce\Listener\SiteCartPluginListener;
use MelisDemoCommerce\Listener\SiteCheckoutCartPluginListener;
use MelisDemoCommerce\Listener\SiteCheckoutConfirmPluginListener;
use MelisDemoCommerce\Listener\SiteOrderPluginListener;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        
        // $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, function($e) {
        //     $viewModel = $e->getViewModel();
        //     $viewModel->setTemplate('layout/errorLayout');
        // });
        
        // $eventManager->attach(MvcEvent::EVENT_RENDER_ERROR, function($e) {
        //     $viewModel = $e->getViewModel();
        //     $viewModel->setTemplate('layout/errorLayout');
        // }); 
        
        // Adding Event listener to customize the Site menu from Plugin
        (new SiteMenuCustomizationListener())->attach($eventManager);
        (new SiteBreadcrumbCustomizationListener())->attach($eventManager);
        
        // Fake payments listener, for testing purposes
        // (new SiteFakePaymentListener())->attach($eventManager);
        // (new SiteFakePaymentProcesstListener())->attach($eventManager);
        // Paypal style payment integration
        (new SiteFakePaypalStyleListener())->attach($eventManager);
        (new SiteFakePaypalStyleProcesstListener())->attach($eventManager);

        (new SiteShipmentCostListener())->attach($eventManager);
        
        // Plugin customization
        (new SiteCommerceCategoryProductListPluginListener())->attach($eventManager);
        (new SiteProductShowPluginListener())->attach($eventManager);
        (new SiteCommerceProductListPluginListener())->attach($eventManager);
        (new SiteCommerceProductPriceRangePluginListener())->attach($eventManager);
        (new SiteCommerceRelatedProductsPluginListener())->attach($eventManager);
        (new SiteCartPluginListener())->attach($eventManager);
        (new SiteCheckoutCartPluginListener())->attach($eventManager);
        (new SiteCheckoutConfirmPluginListener())->attach($eventManager);
        (new SiteOrderPluginListener())->attach($eventManager);
        (new SiteOrderReturnProductPluginListener())->attach($eventManager);
    }
    
    public function getConfig()
    {
        $configDir = __DIR__ . '/config';

        // module.config.php, melis.plugins.config.php and MelisDemoCommerce.config.php
        // are runtime working copies: they are git-ignored and get their [:tokens]
        // rewritten with real ids by the setup wizard. Generate them from their
        // pristine .dist templates if missing so the module can boot on a fresh install.
        $generated = ['module.config.php', 'melis.plugins.config.php', 'MelisDemoCommerce.config.php'];
        foreach ($generated as $file) {
            if (!file_exists($configDir . '/' . $file) && file_exists($configDir . '/' . $file . '.dist')) {
                @copy($configDir . '/' . $file . '.dist', $configDir . '/' . $file);
            }
        }

        $config = [];
        $configFiles = [
            include $configDir . '/module.config.php',
            include $configDir . '/melis.plugins.config.php',
            include $configDir . '/MelisDemoCommerce.config.php',
            include $configDir . '/assets.config.php',
            include $configDir . '/app.forms.php',
        ];

        foreach ($configFiles as $file) {
            $config = ArrayUtils::merge($config, $file);
        }

        return $config;
    }

    public function getAutoloaderConfig()
    {
        return [
            'Laminas\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ],
            ],
        ];
    }
}
