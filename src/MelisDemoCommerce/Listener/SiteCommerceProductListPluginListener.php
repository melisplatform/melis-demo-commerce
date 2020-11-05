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

class SiteCommerceProductListPluginListener extends SiteGeneralListener
{
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
                // Getting the Service Manager from param target
                $this->serviceManager = $e->getTarget()->getServiceManager();

                // Getting the Datas from the Event Parameters
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
        $documentSrv = $this->serviceManager->get('MelisComDocumentService');

        /** @var MelisSiteConfigService $siteConfigSrv */
        $siteConfigSrv = $this->serviceManager->get('MelisSiteConfigService');

        $countryId = $siteConfigSrv->getSiteConfigByKey('site_country_id', $params['pluginFronConfig']['pageId']);

        // Client group
        $ecomAuthSrv = $this->getServiceManager()->get('MelisComAuthenticationService');
        $clientGroup = null;
        if ($ecomAuthSrv->hasIdentity())
            $clientGroup = $ecomAuthSrv->getClientGroup();

        // Getting the Lowest Price of Product variants and
        // Secondary image for slider image hover
        if (!empty($products))
        {
            foreach ($products As $key => $prd)
            {
                // Getting the lowest price of Product or its Variants
                // Getting the List of Variants of the Product
                $prdVar = $melisComProductService->getProductVariants($prd['prd_id'], true);

                $lowestPrice = null;
                $lowestPriceCurrency = null;
                $lowestPriceCurrencyCode = null;
                foreach ($prdVar As $var)
                {
                    if (empty($lowestPrice))
                    {
                        // Getting the Final Price of a variant
                        $varPrice = $melisComVariantService->getVariantFinalPrice($var->var_id, $countryId, $clientGroup);

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
                        $varPrice = $melisComVariantService->getVariantFinalPrice($var->var_id, $countryId, $clientGroup);

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
                    $prdPrice = $melisComProductService->getProductVariantPriceById($prd['prd_id']);

                    if (!empty($prdPrice))
                    {
                        $lowestPrice = $prdPrice->price_net;
                        $lowestPriceCurrency = $prdPrice->cur_symbol;
                        $lowestPriceCurrencyCode = $prdPrice->cur_code;
                    }
                }

                if (!empty($lowestPrice))
                {
                    $customPrice = array(
                        'prd_price_net' => $lowestPrice,
                        'prd_currency_code' => $lowestPriceCurrencyCode,
                        'prd_currency_symbol' => $lowestPriceCurrency,
                    );

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