<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2017 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisDemoCommerce\Listener;


use MelisFront\Service\MelisSiteConfigService;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;

class SiteCommerceRelatedProductsPluginListener implements ListenerAggregateInterface
{
    private $serviceLocator;

    public function attach(EventManagerInterface $events)
    {
        $sharedEvents = $events->getSharedManager();

        $callBackHandler = $sharedEvents->attach(
            '*',
            array(
                'MelisCommerceRelatedProductsPlugin_melistemplating_plugin_end',
            ),
            function($e){
                // Getting the Service Locator from param target
                $this->serviceLocator = $e->getTarget()->getServiceLocator();

                // Getting the Datas from the Event Parameters
                $params = $e->getParams();

                $viewVariables = $params['view']->getVariables();

                if ($params['view']->getTemplate() == 'MelisDemoCommerce/plugin/related-products')
                {
                    $viewVariables['relatedProducts'] = $this->customizeRelatedProductsPrice($viewVariables['relatedProducts'], $params);
                }
            },
            100);

        $this->listeners[] = $callBackHandler;
    }

    public function customizeRelatedProductsPrice($relProducts = array(), $params)
    {
        $melisComVariantService = $this->serviceLocator->get('MelisComVariantService');
        $melisComProductService = $this->serviceLocator->get('MelisComProductService');

        /** @var MelisSiteConfigService $siteConfigSrv */
        $siteConfigSrv = $this->serviceLocator->get('MelisSiteConfigService');

        $countryId = $siteConfigSrv->getSiteConfigByKey('site_country_id', $params['pluginFronConfig']['pageId']);

        if (!empty($relProducts))
        {
            // Getting the Lowest Price of Product variants
            foreach ($relProducts As $key => $val)
            {
                //get product id
                $prd_id = $val->getId();

                //get original price
                $price = $val->display_price;

                // Getting the List of Variants of the Product
                $prdVar = $melisComProductService->getProductVariants($prd_id, true);

                $lowestPrice = null;
                $lowestPriceCurrency = null;
                $lowestPriceCurrencyCode = null;

                foreach ($prdVar As $var)
                {
                    if (empty($lowestPrice))
                    {
                        // Getting the Final Price of a variant
                        $varPrice = $melisComVariantService->getVariantFinalPrice($var->var_id, $countryId);

                        // if the variant has Price base on the Country
                        // this will partially assign as Lowest Prices
                        if (!empty($varPrice))
                        {
                            $lowestPrice = $varPrice->price_net;
                            $lowestPriceCurrency = $varPrice->cur_symbol;
                            $lowestPriceCurrencyCode = $varPrice->cur_code;
                        }
                    }
                    else
                    {
                        // Getting the Final Price of a variant
                        $varPrice = $melisComVariantService->getVariantFinalPrice($var->var_id, $countryId);

                        // if the variant has Price base on the Country
                        if (!empty($varPrice))
                        {
                            // Checking if the Variant Price is Less than the first variant
                            if ($lowestPrice > $varPrice->price_net)
                            {
                                // assigning as the lowest Price to Product Price
                                $lowestPrice = $varPrice->price_net;
                                $lowestPriceCurrency = $varPrice->cur_symbol;
                                $lowestPriceCurrencyCode = $varPrice->cur_code;
                            }
                        }
                    }
                }

                // If the Lowest Price is still null
                // this will try to get from the Product Price
                if (empty($lowestPrice))
                {
                    $prdPrice = $melisComProductService->getProductVariantPriceById($prd_id);

                    if (!empty($prdPrice))
                    {
                        $lowestPrice = $prdPrice->price_net;
                        $lowestPriceCurrency = $prdPrice->cur_symbol;
                        $lowestPriceCurrencyCode = $prdPrice->cur_code;
                    }
                }

                if (!empty($lowestPrice))
                {
                    $price->price_net = $lowestPrice;
                    $price->cur_symbol = $lowestPriceCurrency;
                    $price->cur_code = $lowestPriceCurrencyCode;
                }
            }
        }
        return $relProducts;
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