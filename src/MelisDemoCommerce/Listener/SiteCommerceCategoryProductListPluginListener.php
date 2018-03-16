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
use Zend\Stdlib\ArrayUtils;

class SiteCommerceCategoryProductListPluginListener implements ListenerAggregateInterface
{
    private $serviceLocator;
	
    public function attach(EventManagerInterface $events)
    {
        $sharedEvents = $events->getSharedManager();
        
        $callBackHandler = $sharedEvents->attach(
        	'*',
            array(
                'MelisCommerceCategoryProductListPlugin_melistemplating_plugin_end',
            ),
        	function($e){
        	    // Getting the Service Locator from param target
        	    $this->serviceLocator = $e->getTarget()->getServiceLocator();
        	    
        	    // Getting the Datas from the Event Parameters
        	    $params = $e->getParams();
        	    
        	    $viewVariables = $params['view']->getVariables();
        	    
        	    if ($params['view']->getTemplate() == 'MelisDemoCommerce/plugin/category-product-list-slider')
        	    {
        	        $viewVariables['categoryProducts'] = $this->customizeProductList($viewVariables['categoryProducts']);
        	    }
        	},
        100);
        
        $this->listeners[] = $callBackHandler;
    }
    
    public function customizeProductList($relProducts = array())
    {
        $melisComVariantService = $this->serviceLocator->get('MelisComVariantService');
        $melisComProductService = $this->serviceLocator->get('MelisComProductService');
        $documentSrv = $this->serviceLocator->get('MelisComDocumentService');
        
        // Getting the Site config "MelisDemoCommerce.config.php"
        $siteConfig = $this->serviceLocator->get('config');
        $siteConfig = $siteConfig['site']['MelisDemoCommerce'];
        $siteDatas = $siteConfig['datas'];
        
        $countryId = $siteDatas['site_country_id'];
        
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
                        if (is_null($lowestPrice))
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
                    if (is_null($lowestPrice))
                    {
                        $prdPrice = $melisComProductService->getProductVariantPriceById($prdId);
                        
                        if (!is_null($prdPrice))
                        {
                            $lowestPrice = $prdPrice->price_net;
                            $lowestPriceCurrency = $prdPrice->cur_symbol;
                            $lowestPriceCurrencyCode = $prdPrice->cur_code;
                        }
                    }
                    
                    if (!is_null($lowestPrice))
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
                    
                    // Getting the Secondary Image with the image type of "SMALL"
                    $doc = $documentSrv->getDocumentsByRelationAndTypes('product', $prdId, 'IMG', array('SMALL'));
                    
                    if (!empty($doc))
                    {
                        $customDocs = array(
                            'imageSecondary' => $doc[0]->doc_path,
                            'imageSecondaryType' => $doc[0]->dtype_sub_code
                        );
                        
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
    
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }
}