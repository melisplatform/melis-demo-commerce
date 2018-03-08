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

class SiteOrderPluginListener implements ListenerAggregateInterface
{
    public function attach(EventManagerInterface $events)
    {
        $sharedEvents = $events->getSharedManager();
        
        $callBackHandler = $sharedEvents->attach(
            '*',
            array(
                'MelisCommerceOrderPlugin_melistemplating_plugin_end',
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
                if (in_array($params['view']->getTemplate(), array('MelisDemoCommerce/order-details')))
                {
                    $variantSvc = $sm->get('MelisComVariantService');
                    $documentSvc = $sm->get('MelisComDocumentService');
                    
                    $viewVariables = $params['view']->getVariables();
                    
                    $orderList = $viewVariables->order;
                    
                    if (!empty($orderList['items']))
                    {
                        foreach ($orderList['items'] As $key => $item)
                        {
                            $product = $variantSvc->getProductByVariantId($item['variant_id']);
                            $prdId = $product->prd_id;
                            
                            $orderList['items'][$key]['productImage'] = $documentSrv->getDocDefaultImageFilePath('product', $prdId);
                        }
                        
                        $viewVariables->order = $orderList;
                    }
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