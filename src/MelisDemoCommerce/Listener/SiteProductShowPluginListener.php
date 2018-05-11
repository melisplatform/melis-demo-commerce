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

class SiteProductShowPluginListener implements ListenerAggregateInterface
{
    public function attach(EventManagerInterface $events)
    {
        $sharedEvents      = $events->getSharedManager();
        
        $callBackHandler = $sharedEvents->attach(
            '*',
            array(
                'MelisCommerceProductShowPlugin_melistemplating_plugin_end',
                'MelisCommerceProductShowPlugin_melistemplating_plugin_start',
            ),
            function($e){
                // Getting the Service Locator from param target
                $sm = $e->getTarget()->getServiceLocator();
                
                // Getting the Datas from the Event Parameters
                $params = $e->getParams();

                // Getting the Site config "MelisDemoCommerce.config.php"
                $siteConfig = $sm->get('config');
                $siteConfig = $siteConfig['site']['MelisDemoCommerce'];
                $siteDatas = $siteConfig['datas'];

                if(isset($params['view'])) {
                    // Checking the DemoCommerce template assigned to the ProductShow plugin
                    if ($params['view']->getTemplate() == 'MelisDemoCommerce/plugin/show-product') {
                        $viewVariables = $params['view']->getVariables();

                        $product = $viewVariables->product;

                        $pluginConfig = $params['pluginFronConfig'];

                        if (!empty($product->getId())) {
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

                            $countryId = $siteDatas['site_country_id'];

                            if ($productId) {
                                $variant = $variantSvc->getMainVariantByProductId($productId, $langId, $countryId);

                                if (is_null($variant)) {

                                    $variant = $variantSvc->getVariantListByProductId($productId, $langId, $countryId);
                                    $variant = !empty($variant) ? $variant[0] : $variant;
                                }

                                if (!empty($variant)) {
                                    $tmp = $variant->getAttributeValues();

                                    usort($tmp, function ($a, $b) {
                                        return strcmp($a->atval_attribute_id, $b->atval_attribute_id);
                                    });

                                    foreach ($tmp as $val) {
                                        $action = $val->atval_attribute_id;
                                        $selection[$val->atval_attribute_id] = $val->atval_id;
                                    }

                                    $variantId = $variant->getId();
                                }
                            }

                            $viewRender = $sm->get('ViewRenderer');
                            $pluginManger = $sm->get('ControllerPluginManager');

                            // Adding MelisCommerceAttributesShowPlugin to ProductShowPlugin
                            $attributeShowPlugin = $pluginManger->get('MelisCommerceAttributesShowPlugin');
                            $pluginParams = array(
                                'pageId' => $pluginConfig['pageId'],
                                'id' => 'attributes_' . $pluginConfig['id'],
                                'template_path' => 'MelisDemoCommerce/plugin/show-attributes',
                                'm_product_id' => $product->getId(),
                                'm_selected_attr' => $selection,
                            );

                            $attributePlugin = $attributeShowPlugin->render($pluginParams);
                            $viewVariables->attribute = $viewRender->render($attributePlugin);

                            // Adding MelisCommerceAddToCartPlugin to ProductShowPlugin
                            $addToCartPlugin = $pluginManger->get('MelisCommerceAddToCartPlugin');
                            $pluginParams = array(
                                'pageId' => $pluginConfig['pageId'],
                                'id' => 'addToCart_' . $pluginConfig['id'],
                                'template_path' => 'MelisDemoCommerce/plugin/show-add-to-cart',
                                'm_variant_id' => $variantId,
                                'm_variant_country' => $countryId,
                            );

                            $addToCartPlugin = $addToCartPlugin->render($pluginParams);
                            $viewVariables->addToCart = $viewRender->render($addToCartPlugin);


                            $viewVariables->product_variant = $variant;
                            $viewVariables->currency = $currency;
                        }
                    }
                }else{
                    if(!isset($params['m_product_id'])) {
                        $params['m_product_id'] = $siteDatas['product_show_default_id'];
                        $params['m_product_country'] = $siteDatas['site_country_id'];
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