<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisDemoCommerce\Listener;

use Laminas\EventManager\EventManagerInterface;
use Laminas\Paginator\Paginator;
use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Session\Container;

class SiteCartPluginListener extends SiteGeneralListener
{
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->attachEventListener(
            $events,
            '*',
            [
                'MelisCommerceCartPlugin_melistemplating_plugin_end',
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
            100
        );
    }
}