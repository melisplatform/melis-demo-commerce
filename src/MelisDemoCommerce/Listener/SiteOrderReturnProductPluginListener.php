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

class SiteOrderReturnProductPluginListener extends SiteGeneralListener
{
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->attachEventListener(
            $events,
            '*',
            [
                'MelisCommerceOrderReturnProductPlugin_melistemplating_plugin_end',
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
                if (in_array($params['view']->getTemplate(), array('MelisDemoCommerce/order-return-product')))
                {
                    $viewVariables = $params['view']->getVariables();
                    
                    $returnProducts = $viewVariables->returnProducts;
                    foreach($returnProducts as $orderId => $rProduct) {
                        if (!empty($rProduct['products'])) {
                            foreach ($rProduct['products'] As $key => $item) {
                                $returnProducts[$orderId]['products'][$key]['productImage'] = $documentSrv->getDocDefaultImageFilePath('product', $item['productId']);
                            }
                        }
                    }
                    $viewVariables->returnProducts = $returnProducts;
                }
            },
            100
        );
    }
}