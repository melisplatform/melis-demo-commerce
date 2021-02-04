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
use Laminas\Stdlib\ArrayUtils;

class SiteCommerceCategoryProductListPluginListener extends SiteGeneralListener
{
    private $serviceManager;
    
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->attachEventListener(
            $events,
            '*',
            [
                'MelisCommerceCategoryProductListPlugin_melistemplating_plugin_end',
            ],
            function($e){
                // Getting the Service Manager from param target
                $this->serviceManager = $e->getTarget()->getServiceManager();
                
                // Getting the Datas from the Event Parameters
                $params = $e->getParams();
                
                $viewVariables = $params['view']->getVariables();
                
                if ($params['view']->getTemplate() == 'MelisDemoCommerce/plugin/category-product-list-slider')
                {
                    $viewVariables['categoryProducts'] = $this->customizeProductList($viewVariables['categoryProducts'], $params);
                }
            },
        100);
    }
    
    public function customizeProductList($relProducts = array(), $params)
    {
        $melisComVariantService = $this->serviceManager->get('MelisComVariantService');
        $melisComProductService = $this->serviceManager->get('MelisComProductService');
        $melisComPriceService = $this->serviceManager->get('MelisComPriceService');

        $documentSrv = $this->serviceManager->get('MelisComDocumentService');

        /** @var MelisSiteConfigService $siteConfigSrv */
        $siteConfigSrv = $this->serviceManager->get('MelisSiteConfigService');

        $pageId = $params['pluginFronConfig']['pageId'];

        $countryId = $siteConfigSrv->getSiteConfigByKey('site_country_id', $pageId);

        // Client group
        $ecomAuthSrv = $this->serviceManager->get('MelisComAuthenticationService');
        $clientGroup = null;
        if ($ecomAuthSrv->hasIdentity())
            $clientGroup = $ecomAuthSrv->getClientGroup();

        if (!empty($relProducts))
        {
            // Getting the Lowest Price of Product variants and
            // Secondary image for slider image hover
            foreach ($relProducts As $key => $val)
            {
                $productsPrices = array();

                foreach ($val->products As $pKey => $prd)
                {
                    $prdId = $prd->getId();
                    
                    // Getting the lowest price of Product or its Variants
                    
                    // Getting the List of Variants of the Product
                    $prdVar = $melisComProductService->getProductVariants($prdId, true);

                    $lowestPrice = null;
                    $lowestPriceCurrency = null;
                    $lowestPriceCurrencyCode = null;
                    foreach ($prdVar As $var)
                    {
                        if (empty($lowestPrice))
                        {
                            // Getting the Final Price of a variant
                            $varPrice = $melisComPriceService->getItemPrice($var->var_id, $countryId, $clientGroup, 'variant', ['categoryId' => $val->getId()]);
                            
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
                            // Getting the Final Price of a variant
                            $varPrice = $melisComPriceService->getItemPrice($var->var_id, $countryId, $clientGroup, 'variant', ['categoryId' => $val->getId()]);
                            
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
                    // If the Lowest Price is still null
                    // this will try to get from the Product Price
                    if (empty($lowestPrice))
                    {
                        $prdPrice = $varPrice = $melisComPriceService->getItemPrice($prdId, $countryId, $clientGroup, 'product', ['categoryId' => $val->getId()]);
                        if (!empty($prdPrice['price']))
                        {
                            // $lowestPrice = $prdPrice[0]->price_net;
                            // $lowestPriceCurrency = (!empty($prdPrice[0]->cur_symbol)) ? $prdPrice[0]->cur_symbol : $siteConfigSrv->getSiteConfigByKey('site_currency_symbol', $pageId);
                            // $lowestPriceCurrencyCode = (!empty($prdPrice[0]->cur_code)) ? $prdPrice[0]->cur_code : $siteConfigSrv->getSiteConfigByKey('site_currency_symbol', $pageId);
                            $lowestPrice = $varPrice['price'];
                            $lowestPriceCurrency = $varPrice['price_currency']['symbol'];
                            $lowestPriceCurrencyCode = $varPrice['price_currency']['code'];
                        }
                    }
                    
                    if (!empty($lowestPrice))
                    {
                        $customPrice = array(
                            'prd_price_net' => $lowestPrice,
                            'prd_currency_code' => $lowestPriceCurrencyCode,
                            'prd_currency_symbol' => $lowestPriceCurrency,
                        );
                        
                        $relProducts[$key]->products[$pKey]->setPrice($customPrice);
                        
                        // adding product price as index and product id as value in-order to sort
                        $productsPrices[$lowestPrice] = $prdId;
                    }


                    //process the product image
                    if(!empty($prd->getDocuments())){
                        $customDocs = array(
                            'imageSecondary' => '',
                            'imageSecondaryType' => ''
                        );

                        // Getting the Secondary Image with the image type of "SMALL"
                        $doc = $documentSrv->getDocumentsByRelationAndTypes('product', $prdId, 'IMG', array('SMALL'));
                        if (!empty($doc)) {
                            $customDocs['imageSecondary'] = $doc[0]->doc_path;
                            $customDocs['imageSecondaryType'] = $doc[0]->dtype_sub_code;
                        }

                        foreach ($prd->getDocuments() As $dVal)
                        {
                            if ($dVal->dtype_sub_code == 'DEFAULT')
                            {
                                $docs = ArrayUtils::merge((Array)$dVal, $customDocs);
                            }
                        }
                        $relProducts[$key]->products[$pKey]->setDocuments($docs);
                    }
                    
                }
                
                // Sorting product prices from lowest to highest price
                $relProducts[$key]->products = $this->sortProductsByPrices($relProducts[$key]->products, $productsPrices);
            }
        }
        
        return $relProducts;
    }
    
    /**
     * Sorting product prices from lowest to highest price
     * 
     * @param array $relProducts
     * @param array $productsPrices
     * @return array
     */
    private function sortProductsByPrices($relProducts, $productsPrices)
    {
        $finalProducsSorted = array();
        
        if (!empty($productsPrices))
        {
            ksort($productsPrices);
            
            foreach ($productsPrices As $key => $val)
            {
                foreach ($relProducts As $pVal)
                {
                    if ($val == $pVal->getId())
                    {
                        array_push($finalProducsSorted, $pVal);
                    }
                }
            }
        }
        
        return $finalProducsSorted;
    }
    
    /**
     * Array sorter using array index
     * @param Array $array, the list to be sort
     * @param String $on, string index to sort
     * @param String $order, type of sorting
     * @return Array
     */
    function array_sort($array, $on, $order = SORT_ASC){
    
        $new_array = array();
        $sortable_array = array();
    
        if (count($array) > 0) {
            foreach ($array as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $k2 => $v2) {
                        if ($k2 == $on) {
                            $sortable_array[$k] = $v2;
                        }
                    }
                } else {
                    $sortable_array[$k] = $v;
                }
            }
    
            switch ($order) {
                case SORT_ASC:
                    asort($sortable_array);
                    break;
                case SORT_DESC:
                    arsort($sortable_array);
                    break;
            }
    
            foreach ($sortable_array as $k => $v) {
                $new_array[$k] = $array[$k];
            }
        }
    
        return $new_array;
    }
}