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
use Laminas\Paginator\Paginator;
use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Session\Container;

class SiteCommerceProductListPluginListener extends SiteGeneralListener
{
    private $event;
    private $serviceManager;

    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->attachEventListener(
            $events,
            '*',
            [
                'MelisCommerceProductListPlugin_melistemplating_plugin_end',
            ],
            function($e){
                $this->event = $e;
                // Getting the Service Manager from param target
                $this->serviceManager = $e->getTarget()->getServiceManager();

                // Getting the Data from the Event Parameters
                $params = $e->getParams();

                if (!empty($params['view']->getTemplate() && !empty($params['view']->getVariables()['categoryListProducts'])))
                {
                    if ($params['view']->getTemplate() == 'MelisDemoCommerce/plugin/product-list')
                    {
                        $categoryListProd = $params['view']->getVariables()['categoryListProducts'];
                        $products = $categoryListProd->getAdapter()->getItems(0, $categoryListProd->getAdapter()->count());
                        $pageCurrent = $categoryListProd->getCurrentPageNumber();
                        $pageNbPerPage = $categoryListProd->getItemCountPerPage();
                        $products = $this->customizeProductList($products, $params);

                        $paginator = new Paginator(new ArrayAdapter($products));
                        $paginator->setCurrentPageNumber($pageCurrent)
                            ->setItemCountPerPage($pageNbPerPage);

                        $params['view']->getVariables()['categoryListProducts'] = $paginator;
                    }
                }
            },
            100
        );

    }

    public function customizeProductList($products, $params)
    {
        $melisComVariantService = $this->serviceManager->get('MelisComVariantService');
        $melisComProductService = $this->serviceManager->get('MelisComProductService');
        $melisComPriceService = $this->serviceManager->get('MelisComPriceService');
        
        $documentSrv = $this->serviceManager->get('MelisComDocumentService');

        /** @var MelisSiteConfigService $siteConfigSrv */
        $siteConfigSrv = $this->serviceManager->get('MelisSiteConfigService');

        $countryId = $siteConfigSrv->getSiteConfigByKey('site_country_id', $params['pluginFrontConfig']['pageId']);

        // Client group
        $ecomAuthSrv = $this->serviceManager->get('MelisComAuthenticationService');
        $clientGroup = 1;
        if ($ecomAuthSrv->hasIdentity())
            $clientGroup = $ecomAuthSrv->getClientGroup();

        $routeMatch = $this->serviceManager->get('router')->match($this->serviceManager->get('request'));
        $routeParams = $routeMatch->getParams();

        $melisComCategoryService = $this->serviceManager->get('MelisComCategoryService');

        $currentCategoryId = null;
        $container = new Container('melisplugins');
        $langId = $container['melis-plugins-lang-id'];

        if (!empty($routeParams['categoryId'])) {
            $currentCategoryId = $routeParams['categoryId'];
        }
        
        // dump($currentCategoryOrder);
        // Getting the Lowest Price of Product variants and
        // Secondary image for slider image hover
        if (!empty($products))
        {
            foreach ($products As $key => $prd)
            {
                $categoryDiscount = [];

                if ($this->serviceManager->has('MelisCommerceGroupDiscountPerCategoryService')) {

                    $currentPrdCats = [];
                    foreach ($prd['prd_categories'] As $prdCat)
                        $currentPrdCats[] = $prdCat['cat_id'];

                    $matchCurrentCat = false;
                    $catMatches = [];
                    foreach ($prd['prd_categories'] As $prdCat) {

                        if ($prdCat['cat_id'] == $currentCategoryId) {

                            $matchCurrentCat = true;

                            $catChildren = $this->categoryIdIterator($melisComCategoryService->getAllSubCategoryIdById($prdCat['cat_id'], true));
                            
                            foreach($catChildren As $catChild) {
                                if (in_array($catChild, $currentPrdCats))
                                    $catMatches[] = $catChild;
                            }
                            break;

                        } 
                    }

                    if (empty($catMatches)) {
                        $catChildren = $this->categoryIdIterator($melisComCategoryService->getAllSubCategoryIdById($currentCategoryId, true));
                        foreach($catChildren As $catChild) {
                            if (in_array($catChild, $currentPrdCats))
                                $catMatches[] = $catChild;
                        }
                    }
                    

                    if (!empty($catMatches)) {

                        foreach ($catMatches As $cat) {
                            $catDiscount = $this->serviceManager->get('MelisCommerceGroupDiscountPerCategoryService')
                                        ->getCategoryDiscount($cat, $countryId, $clientGroup);

                            if (empty($categoryDiscount) && !empty($catDiscount)){
                                $categoryDiscount = $catDiscount;
                            } elseif (!empty($categoryDiscount) && !empty($catDiscount)) {
                                if ($catDiscount->gdc_discount_percentage > $categoryDiscount->gdc_discount_percentage)
                                    $categoryDiscount = $catDiscount;
                            }
                        }
                    }
    
                    if (!empty($categoryDiscount)) {
                        $categoryDiscount = [
                            'categoryId' => $categoryDiscount->gdc_category_id
                        ];
                    }
                }

                // Getting the lowest price of Product or its Variants
                // Getting the List of Variants of the Product
                $prdVar = $melisComProductService->getProductVariants($prd['prd_id'], true);

                $lowestPrice = null;
                $lowestPriceCurrency = null;
                $lowestPriceCurrencyCode = null;
                $productVariantPrice = null;

                foreach ($prdVar As $var)
                {
                    // Getting the Final Price of a variant
                    $varPrice = $melisComPriceService->getItemPrice($var->var_id, $countryId, $clientGroup, 'variant', $categoryDiscount);

                    if (empty($lowestPrice))
                    {
                        // if the variant has Price base on the Country
                        // this will partially assign as Lowest Prices
                        if (!empty($varPrice['price']))
                        {
                            $lowestPrice = $varPrice['price'];
                            $lowestPriceCurrency = $varPrice['price_currency']['symbol'];
                            $lowestPriceCurrencyCode = $varPrice['price_currency']['code'];
                            $productVariantPrice = $varPrice;
                        }
                    }
                    else
                    {
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
                                $productVariantPrice = $varPrice;
                            }
                        }
                    }
                }

                // If the Lowest Price is still null
                // this will try to get from the Product Price
                if (empty($lowestPrice))
                {
                    // Product price
                    $prdVarPrice = $melisComPriceService->getItemPrice($prd['prd_id'], $countryId, $clientGroup, 'product', $categoryDiscount);

                    if (!empty($prdVarPrice['price']))
                    {
                        $lowestPrice = $prdVarPrice['price'];
                        $lowestPriceCurrency = $prdVarPrice['price_currency']['symbol'];
                        $lowestPriceCurrencyCode = $prdVarPrice['price_currency']['code'];
                        $productVariantPrice = $prdVarPrice;
                    }
                }

                if (!empty($lowestPrice))
                {
                    $customPrice = array(
                        'prd_price_net' => $lowestPrice,
                        'prd_currency_code' => $lowestPriceCurrencyCode,
                        'prd_currency_symbol' => $lowestPriceCurrency,
                        'prd_price' => $productVariantPrice,
                    );

                    if (!empty($productVariantPrice['surcharge_module']))
                        foreach($productVariantPrice['surcharge_module'] As $dis) {
                            $customPrice['category_discount'] = [
                                'category_id' => $dis['category_associated'],
                                'label' => $melisComCategoryService->getCategoryNameById($dis['category_associated'], $langId) .' / '. $dis['discount_percentage'].'%',
                            ];
                            break;
                        }

                    $products[$key]['prd_price_details'] = $customPrice;
                }

                // Getting the Secondary Image with the image type of "SMALL"
                $doc = $documentSrv->getDocumentsByRelationAndTypes('product', $prd['prd_id'], 'IMG', array('SMALL'));

                if (!empty($doc))
                {
                    $customDocs = array(
                        'imageSecondary' => $doc[0]->doc_path,
                        'imageSecondaryType' => $doc[0]->dtype_sub_code
                    );

                    $temp = ArrayUtils::merge($prd['prd_docs_image'], $customDocs);

                    $products[$key]['prd_docs_image'] = $temp;
                }
            }
        }

        return $products;
    }

    /**
     * Recursive function to retrieve category ids
     * @param [] $categories array of categories
     * @return $categoryId[]
     */
    private function categoryIdIterator($categories)
    {
        $categoryId = array();
        foreach($categories as $category){
            $categoryId[] = $category['cat_id'];
            if(is_array($category['cat_children'])){
                $categoryId = array_merge($categoryId, $this->categoryIdIterator($category['cat_children']));
            }
        }
        return $categoryId;
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