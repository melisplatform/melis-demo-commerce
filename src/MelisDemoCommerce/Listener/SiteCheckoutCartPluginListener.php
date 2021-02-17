<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisDemoCommerce\Listener;

use Laminas\EventManager\EventManagerInterface;
use Laminas\Session\Container;

class SiteCheckoutCartPluginListener extends SiteGeneralListener
{
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->attachEventListener(
            $events,
            '*',
            [
                'MelisCommerceCheckoutCartPlugin_melistemplating_plugin_end',
            ],
            function($e){
                // Getting the Service Manager from param target
                $sm = $e->getTarget()->getServiceManager();
                
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
            100
        );
    }
}