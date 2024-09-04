<?php

namespace MelisDemoCommerce\Listener;


use Laminas\EventManager\EventManagerInterface;

class SiteCommerceProductPriceRangePluginListener extends SiteGeneralListener
{
    private $serviceManager;

    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->attachEventListener(
            $events,
            '*',
            [
                'MelisCommerceProductPriceRangePlugin_melistemplating_plugin_end',
            ],
            function($e){
                // Getting the Service Manager from param target
                $this->serviceManager = $e->getTarget()->getServiceManager();

                // Getting the Datas from the Event Para meters
                $params = $e->getParams();

                $viewVariables = $params['view']->getVariables();

                if ($params['view']->getTemplate() == 'MelisDemoCommerce/plugin/product-price-range')
                {
                    $viewVariables['filterMenuPriceValue'] = $this->processProductPrice($viewVariables['filterMenuPriceValue']);
                }
            },
            100
        );
    }

    private function processProductPrice($filterMenuPriceValue)
    {
        $req = $this->serviceManager->get('request');
        $fromFilter = $req->getQuery('m_box_product_price_max');

        // Retrieving the default Values for Product prices
        $proService = $this->serviceManager->get('MelisComProductService');
        $priceMin = $proService->getMaximumMinimumPrice('min', $filterMenuPriceValue['m_box_product_price_column'], 'variant');
        $priceMax = $proService->getMaximumMinimumPrice('max', $filterMenuPriceValue['m_box_product_price_column'], 'variant');

        if(is_null($fromFilter))
        {
            $filterMenuPriceValue['m_box_product_price_min'] = ($filterMenuPriceValue['m_box_product_price_min'] <= $priceMin->min_price && $filterMenuPriceValue['m_box_product_price_min'] != 0) ? (int) $filterMenuPriceValue['m_box_product_price_min'] : (int) $priceMin->min_price;
            $filterMenuPriceValue['m_box_product_price_max'] = ($filterMenuPriceValue['m_box_product_price_max'] >= $priceMax->max_price && $filterMenuPriceValue['m_box_product_price_max'] != 0) ? (int) $filterMenuPriceValue['m_box_product_price_max'] : (int) $priceMax->max_price;
        }

        $filterMenuPriceValue['default_min_price'] = ($filterMenuPriceValue['default_min_price'] <= $priceMin->min_price && $filterMenuPriceValue['default_min_price'] != 0) ? (int) $filterMenuPriceValue['default_min_price'] : (int) $priceMin->min_price;
        $filterMenuPriceValue['default_max_price'] = ($filterMenuPriceValue['default_max_price'] >= $priceMax->max_price && $filterMenuPriceValue['default_max_price'] != 0) ? (int) $filterMenuPriceValue['default_max_price'] : (int) $priceMax->max_price;

        return $filterMenuPriceValue;
    }
}