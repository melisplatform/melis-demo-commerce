<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2017 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisDemoCommerce\Listener;


use MelisFront\Service\MelisSiteConfigService;
use Laminas\EventManager\EventManagerInterface;

class SiteCommerceRelatedProductsPluginListener extends SiteGeneralListener
{
    private $serviceManager;

    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->attachEventListener(
            $events,
            '*',
            [
                'MelisCommerceRelatedProductsPlugin_melistemplating_plugin_end',
            ],
            function($e){
                // Getting the Service Manager from param target
                $this->serviceManager = $e->getTarget()->getServiceManager();

                // Getting the Datas from the Event Parameters
                $params = $e->getParams();

                $viewVariables = $params['view']->getVariables();

                if ($params['view']->getTemplate() == 'MelisDemoCommerce/plugin/related-products')
                {
                    $viewVariables['relatedProducts'] = $this->customizeRelatedProductsPrice($params, $viewVariables['relatedProducts']);
                }
            },
            100
        );
    }

    public function customizeRelatedProductsPrice($params, $relProducts = array())
    {
        $melisComVariantService = $this->serviceManager->get('MelisComVariantService');
        $melisComProductService = $this->serviceManager->get('MelisComProductService');
        $melisComPriceService = $this->serviceManager->get('MelisComPriceService');

        /** @var MelisSiteConfigService $siteConfigSrv */
        $siteConfigSrv = $this->serviceManager->get('MelisSiteConfigService');

        $countryId = $siteConfigSrv->getSiteConfigByKey('site_country_id', $params['pluginFrontConfig']['pageId']);

        // Client group
        $ecomAuthSrv = $this->serviceManager->get('MelisComAuthenticationService');
        $clientGroup = 1;
        if ($ecomAuthSrv->hasIdentity())
            $clientGroup = $ecomAuthSrv->getClientGroup();

        if (!empty($relProducts))
        {
            // Getting the Lowest Price of Product variants
            foreach ($relProducts As $key => $val)
            {
                //get product id
                $prd_id = $val->getId();

                //get original price
                $price = [];

                // Getting the List of Variants of the Product
                $prdVar = $melisComProductService->getProductVariants($prd_id, true);

                $lowestPrice = null;
                $lowestPriceCurrency = null;
                $lowestPriceCurrencyCode = null;

                foreach ($prdVar As $var)
                {
                    // Getting the Final Price of a variant
                    $varPrice = $melisComPriceService->getItemPrice($var->var_id, $countryId, $clientGroup);

                    if (empty($lowestPrice))
                    {
                        /**
                         * variant price is empty on given group, try to get the price from general group
                         */
                        if(empty($varPrice['price']))
                            $varPrice = $melisComPriceService->getItemPrice($var->var_id, $countryId, $clientGroup);
                            // $varPrice = $melisComVariantService->getVariantFinalPrice($var->var_id, $countryId, $clientGroup);

                        // if the variant has Price base on the Country
                        // this will partially assign as Lowest Prices
                        if (!empty($varPrice['price']))
                        {
                            $lowestPrice = $varPrice['price'];
                            $lowestPriceCurrency = $varPrice['price_currency']['symbol'];
                            $lowestPriceCurrencyCode = $varPrice['price_currency']['code'];
                        }
                    }
                    else
                    {
                        /**
                         * variant price is empty on given group, try to get the price from general group
                         */
                        if(empty($varPrice['price']))
                            $varPrice = $melisComPriceService->getItemPrice($var->var_id, $countryId, $clientGroup);
                            // $varPrice = $melisComVariantService->getVariantFinalPrice($var->var_id, $countryId, $clientGroup);

                        // if the variant has Price base on the Country
                        if (!empty($varPrice['price']))
                        {
                            // Checking if the Variant Price is Less than the first variant
                            if ($lowestPrice > $varPrice['price'])
                            {
                                // assigning as the lowest Price to Product Price
                                $lowestPrice = $varPrice['price'];
                                $lowestPriceCurrency = $varPrice['price_currency']['symbol'];
                                $lowestPriceCurrencyCode = $varPrice['price_currency']['code'];
                            }
                        }
                    }
                }
                //use the group price instead of one in the general if group price is not null
                // if(!empty($groupPrice)) {
                //     $lowestPrice = $groupPrice;
                // }

                // If the Lowest Price is still null
                // this will try to get from the Product Price
                if (empty($lowestPrice))
                {
                    // Product price
                    $prdVarPrice = $melisComPriceService->getItemPrice($prd_id, $countryId, $clientGroup, 'product');

                    if (!empty($prdVarPrice['price']))
                    {
                        $lowestPrice = $prdVarPrice['price'];
                        $lowestPriceCurrency = $prdVarPrice['price_currency']['symbol'];
                        $lowestPriceCurrencyCode = $prdVarPrice['price_currency']['code'];
                    }
                }

                if (!empty($lowestPrice))
                {
                    $price['price_net'] = $lowestPrice;
                    $price['cur_symbol'] = $lowestPriceCurrency;
                    $price['cur_code'] = $lowestPriceCurrencyCode;
                    
                    $relProducts[$key]->display_price = $price;
                }else{
                    $relProducts[$key]->display_price = null;
                }
            }
        }
        return $relProducts;
    }
}