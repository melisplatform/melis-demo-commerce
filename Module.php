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
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\ModuleManager;
use Zend\Stdlib\ArrayUtils;
use MelisDemoCommerce\Listener\SiteMenuCustomizationListener;
use MelisDemoCommerce\Listener\SiteBreadcrumbCustomizationListener;
// use MelisDemoCommerce\Listener\SiteFakePaymentListener;
// use MelisDemoCommerce\Listener\SiteFakePaymentProcesstListener;
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
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        
        $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, function($e) {
        	$viewModel = $e->getViewModel();
        	$viewModel->setTemplate('layout/errorLayout');
        });
        $eventManager->attach(MvcEvent::EVENT_RENDER_ERROR, function($e) {
        	$viewModel = $e->getViewModel();
        	$viewModel->setTemplate('layout/errorLayout');
        }); 
        
        // Adding Event listener to customize the Site menu from Plugin
        $eventManager->attach(new SiteMenuCustomizationListener());
        $eventManager->attach(new SiteBreadcrumbCustomizationListener());
        
        // Fake payments listener, for testing purposes
//         $eventManager->attach(new SiteFakePaymentListener());
//         $eventManager->attach(new SiteFakePaymentProcesstListener());
        // Paypal style payment integration
        $eventManager->attach(new SiteFakePaypalStyleListener());
        $eventManager->attach(new SiteFakePaypalStyleProcesstListener());
        
        $eventManager->attach(new SiteShipmentCostListener());
        
        // Plugin customization
        $eventManager->attach(new SiteCommerceCategoryProductListPluginListener());
        $eventManager->attach(new SiteProductShowPluginListener());
        $eventManager->attach(new SiteCommerceProductListPluginListener());
        $eventManager->attach(new SiteCommerceProductPriceRangePluginListener());
        $eventManager->attach(new SiteCommerceRelatedProductsPluginListener());
        $eventManager->attach(new SiteCartPluginListener());
        $eventManager->attach(new SiteCheckoutCartPluginListener());
        $eventManager->attach(new SiteCheckoutConfirmPluginListener());
        $eventManager->attach(new SiteOrderPluginListener());
        
        $this->mvcEventErrors($eventManager);
    }
    
    public function mvcEventErrors($eventManager)
    {
        $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, function($e) {
            $viewModel = $e->getViewModel();
            $viewModel->setTemplate('layout/errorLayout');
             
            /* print_r($e->getError()).'<br>';
            print_r($e->getControllerClass()).'<br>';
            print_r($e->getParam('exception')->getMessage()); */
    
            $exception = $e->getParam('exception');
            
            if (is_object($exception))
                die($exception->getMessage());
            else
                die($exception);
            
        });
        
        $eventManager->attach(MvcEvent::EVENT_RENDER_ERROR, function($e) {
            $viewModel = $e->getViewModel();
            $viewModel->setTemplate('layout/errorLayout');
            
            $exception = $e->getParam('exception');
            
            if (is_object($exception))
                die($exception->getMessage());
            else
                die($exception);
        });
    }
    
    public function init(ModuleManager $manager)
    {
        
    }

    public function getConfig()
    {
    	$config = array();
    	$configFiles = array(
    			include __DIR__ . '/config/module.config.php',
    			include __DIR__ . '/config/MelisDemoCommerce.config.php',
    			include __DIR__ . '/config/app.forms.php',
    	);
    	
    	foreach ($configFiles as $file) {
    		$config = ArrayUtils::merge($config, $file);
    	} 
    	
    	return $config;
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
}
