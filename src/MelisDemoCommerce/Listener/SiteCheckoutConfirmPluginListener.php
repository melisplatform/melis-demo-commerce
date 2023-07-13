<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisDemoCommerce\Listener;

use MelisFront\Service\MelisSiteConfigService;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Session\Container;

class SiteCheckoutConfirmPluginListener extends SiteGeneralListener
{
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->attachEventListener(
            $events,
            '*',
            [
                'MelisCommerceCheckoutConfirmPlugin_melistemplating_plugin_end',
            ],
            function($e){
                // Getting the Service Manager from param target
                $sm = $e->getTarget()->getServiceManager();
                
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
                    $imgPreview = $siteConfigSrv->getSiteConfigByKey('image_preview', $params['pluginFrontConfig']['pageId']);
                    $defaultImage = $siteConfigSrv->getSiteConfigByKey('default_image', $params['pluginFrontConfig']['pageId']);
                    foreach ($orderList As $key => $item)
                    {
                        $item->{'product_image'} = $variantSvc->getFinalVariantImage($item->obas_variant_id, array($imgPreview), $defaultImage);
                    }
                    
                    $viewVariables->orderList = $orderList;
                }
            },
            100
        );
    }
}