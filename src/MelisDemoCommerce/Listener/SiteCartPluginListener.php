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
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Session\Container;

class SiteCartPluginListener implements ListenerAggregateInterface
{
    public function attach(EventManagerInterface $events)
    {
        $sharedEvents      = $events->getSharedManager();
        
        $callBackHandler = $sharedEvents->attach(
            '*',
            array(
                'MelisCommerceCartPlugin_melistemplating_plugin_end',
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
                if (in_array($params['view']->getTemplate(), array('MelisDemoCommerce/plugin/my-cart', 'MelisDemoCommerce/plugin/menu-cart')))
                {
                    $viewVariables = $params['view']->getVariables();
                    
                    $cartList = $viewVariables->cartList;
                    
                    $list = array();
                    foreach ($cartList->getAdapter()->getItems(0, null) As $item)
                    {
                        $variant = $item;
                        $variant['name'] = $prodSvc->getProductName($item['product_id'], $langId);
                        $variant['image'] = $documentSrv->getDocDefaultImageFilePath('product', $item['product_id']);
                        array_push($list, $variant);
                    }
                    
                    $paginator = new Paginator(new ArrayAdapter($list));
                    $paginator->setCurrentPageNumber($cartList->getCurrentPageNumber())
                                ->setItemCountPerPage($cartList->getItemCountPerPage());
                    
                    $viewVariables->cartList = $paginator;
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