<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisDemoCommerce\Listener;

use MelisFront\Service\MelisSiteConfigService;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Session\Container;

class SiteCheckoutConfirmPluginListener implements ListenerAggregateInterface
{
    public function attach(EventManagerInterface $events)
    {
        $sharedEvents      = $events->getSharedManager();
        
        $callBackHandler = $sharedEvents->attach(
            '*',
            array(
                'MelisCommerceCheckoutConfirmPlugin_melistemplating_plugin_end',
            ),
            function($e){
                // Getting the Service Locator from param target
                $sm = $e->getTarget()->getServiceLocator();
                
                // Getting the Datas from the Event Parameters
                $params = $e->getParams();

                /** @var MelisSiteConfigService $siteConfigSrv */
                $siteConfigSrv = $sm->get('MelisSiteConfigService');
                
                $container = new Container('melisplugins');
                $langId = $container['melis-plugins-lang-id'];
                
                $prodSvc = $sm->get('MelisComProductService');
                $documentSrv = $sm->get('MelisComDocumentService');
                
                // Checking the Plugin template for customization
                if (in_array($params['view']->getTemplate(), array('MelisDemoCommerce/plugin/checkout-confirmation')))
                {
                    
                    $variantSvc = $sm->get('MelisComVariantService');
                    
                    $viewVariables = $params['view']->getVariables();
                    
                    $orderList = $viewVariables->orderBasket;
                    $imgPreview = $siteConfigSrv->getSiteConfigByKey('image_preview', $params['idPage']);
                    $defaultImage = $siteConfigSrv->getSiteConfigByKey('default_image', $params['idPage']);
                    foreach ($orderList As $key => $item)
                    {
                        $item->{'product_image'} = $variantSvc->getFinalVariantImage($item->obas_variant_id, array($imgPreview), $defaultImage);
                    }
                    
                    $viewVariables->orderList = $orderList;
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