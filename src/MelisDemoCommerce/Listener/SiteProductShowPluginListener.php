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

class SiteProductShowPluginListener extends SiteGeneralListener
{
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->attachEventListener(
            $events,
            '*',
            [
                'MelisCommerceProductShowPlugin_melistemplating_plugin_end',
            ],
            function($e){
                // Getting the Service Manager from param target
                $sm = $e->getTarget()->getServiceManager();
                
                // Getting the Datas from the Event Parameters
                $params = $e->getParams();
                
                // Checking the DemoCommerce template assigned to the ProductShow plugin
                if ($params['view']->getTemplate() == 'MelisDemoCommerce/plugin/show-product')
                {
                    /** @var MelisSiteConfigService $siteConfigSrv */
                    $siteConfigSrv = $sm->get('MelisSiteConfigService');

                    $viewVariables = $params['view']->getVariables();
                    
                    $product = $viewVariables->product;
                    
                    $pluginConfig = $params['pluginFronConfig'];
                    
                    if (!empty($product->getId()))
                    {
                        $productId = $product->getId();
                        
                        $container = new Container('melisplugins');
                        $langId = $container['melis-plugins-lang-id'];
                        
                        $variantSvc = $sm->get('MelisComVariantService');
                        $currencySvc = $sm->get('MelisComCurrencyService');
                        $currency = $currencySvc->getDefaultCurrency();
                        
                        $variant = array();
                        $variantId = null;
                        $action = null;
                        $selection = array();

                        $countryId = $siteConfigSrv->getSiteConfigByKey('site_country_id', $params['pluginFronConfig']['pageId']);

                        $ecomAuthSrv = $sm->get('MelisComAuthenticationService');
                        $clientGroupId = 1;
                        if ($ecomAuthSrv->hasIdentity())
                            $clientGroupId = $ecomAuthSrv->getClientGroup();

                        if ($productId)
                        {
                            $variant = $variantSvc->getMainVariantByProductId($productId, $langId, $countryId, $clientGroupId);
                            
                            if(is_null($variant))
                            {
                                $variant = $variantSvc->getVariantListByProductId($productId, $langId, $countryId, $clientGroupId);
                                $variant = !empty($variant)? $variant[0] : $variant;
                            }
                            
                            if(!empty($variant))
                            {
                                $tmp = $variant->getAttributeValues();
                                
                                usort($tmp, function($a, $b)
                                {
                                    return strcmp($a->atval_attribute_id, $b->atval_attribute_id);
                                });
                                
                                foreach($tmp as $val)
                                {
                                    $action = $val->atval_attribute_id;
                                    $selection[$val->atval_attribute_id] = $val->atval_id;
                                }
                                
                                $variantId = $variant->getId();
                            }
                        }
                        
                        $viewRender = $sm->get('ViewRenderer');
                        $pluginManger = $sm->get('ControllerPluginManager');
                        
                        // Adding MelisCommerceAttributesShowPlugin to ProductShowPlugin
                        $attributeShowPlugin =  $pluginManger->get('MelisCommerceAttributesShowPlugin');
                        $pluginParams = array(
                            'pageId' => $pluginConfig['pageId'],
                            'id' => 'attributes_'.$pluginConfig['id'],
                            'template_path' => 'MelisDemoCommerce/plugin/show-attributes',
                            'm_product_id' => $product->getId(),
                            'm_selected_attr' => $selection,
                        );
                        
                        $attributePlugin = $attributeShowPlugin->render($pluginParams);
                        $viewVariables->attribute = $viewRender->render($attributePlugin);
                        
                        // Adding MelisCommerceAddToCartPlugin to ProductShowPlugin
                        $addToCartPlugin =  $pluginManger->get('MelisCommerceAddToCartPlugin');
                        $pluginParams = array(
                            'pageId' => $pluginConfig['pageId'],
                            'id' => 'addToCart_'.$pluginConfig['id'],
                            'template_path' => 'MelisDemoCommerce/plugin/show-add-to-cart',
                            'm_variant_id' => $variantId,
                            'm_variant_country' => $countryId,
                        );
                        
                        $addToCartPlugin = $addToCartPlugin->render($pluginParams);
                        $viewVariables->addToCart = $viewRender->render($addToCartPlugin);

                        // get list of active variants
                        $activeVariants = $variantSvc->getVariantListByProductId($productId, $langId, $countryId, $clientGroupId, true);
                        $viewVariables->product_variant = $variant;
                        $viewVariables->currency = $currency;
                        $viewVariables->hasVariant = !empty($activeVariants) ? true : false;
                    }
                }
            },
            100
        );
    }
}