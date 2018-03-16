<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2017 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisDemoCommerce\Listener;


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
                    $viewVariables['relatedProducts'] = $this->customizeRelatedProductsPrice($viewVariables['relatedProducts']);
                }
            },
            100);

        $this->listeners[] = $callBackHandler;
    }

    public function customizeRelatedProductsPrice($relProducts = array())
    {
        $melisComVariantService = $this->serviceLocator->get('MelisComVariantService');
        $melisComProductService = $this->serviceLocator->get('MelisComProductService');

        // Getting the Site config "MelisDemoCommerce.config.php"
        $siteConfig = $this->serviceLocator->get('config');
        $siteConfig = $siteConfig['site']['MelisDemoCommerce'];
        $siteDatas = $siteConfig['datas'];

        $countryId = $siteDatas['site_country_id'];

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
                    if (is_null($lowestPrice))
                    {
                        // Getting the Final Price of a variant
                        $varPrice = $melisComVariantService->getVariantFinalPrice($var->var_id, $countryId);

                        // if the variant has Price base on the Country
                        // this will partially assign as Lowest Prices
                        if (!is_null($varPrice))
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
                        if (!is_null($varPrice))
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
                if (is_null($lowestPrice))
                {
                    $prdPrice = $melisComProductService->getProductVariantPriceById($prd_id);

                    if (!is_null($prdPrice))
                    {
                        $lowestPrice = $prdPrice->price_net;
                        $lowestPriceCurrency = $prdPrice->cur_symbol;
                        $lowestPriceCurrencyCode = $prdPrice->cur_code;
                    }
                }

                if (!is_null($lowestPrice))
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