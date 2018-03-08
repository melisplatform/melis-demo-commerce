<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisDemoCommerce\Listener;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Session\Container;

class SiteCheckoutCartPluginListener implements ListenerAggregateInterface
{
    public function attach(EventManagerInterface $events)
    {
        $sharedEvents      = $events->getSharedManager();
        
        $callBackHandler = $sharedEvents->attach(
            '*',
            array(
                'MelisCommerceCheckoutCartPlugin_melistemplating_plugin_end',
            ),
            function($e){
                // Getting the Service Locator from param target
                $sm = $e->getTarget()->getServiceLocator();
                
                // Getting the Datas from the Event Parameters
                $params = $e->getParams();
                
                $container = new Container('melisplugins');
                $langId = $container['melis-plugins-lang-id'];
                
                $prodSvc = $sm->get('MelisComProductService');
                $documentSrv = $sm->get('MelisComDocumentService');
                
                // Checking the Plugin template for customization
                if ($params['view']->getTemplate() == 'MelisDemoCommerce/plugin/checkout-cart')
                {
                    $viewVariables = $params['view']->getVariables();
                    
                    $checkOutCart = $viewVariables->checkOutCart;
                    
                    $list = array();
                    foreach ($checkOutCart As $item)
                    {
                        $variant = $item;
                        $variant['product_name'] = $prodSvc->getProductName($item['var_product_id'], $langId);
                        $variant['product_img'] = $documentSrv->getDocDefaultImageFilePath('product', $item['var_product_id']);
                        array_push($list, $variant);
                    }
                    
                    $viewVariables->checkOutCart = $list; 
                }
            },
            100);
        
        $this->listeners[] = $callBackHandler;
    }
    
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }
}