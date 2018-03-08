<?php

namespace MelisDemoCommerce\Listener;


use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;

class SiteCommerceProductPriceRangePluginListener implements ListenerAggregateInterface
{
    private $serviceLocator;

    public function attach(EventManagerInterface $events)
    {
        $sharedEvents = $events->getSharedManager();

        $callBackHandler = $sharedEvents->attach(
            '*',
            array(
                'MelisCommerceProductPriceRangePlugin_melistemplating_plugin_end',
            ),
            function($e){
                // Getting the Service Locator from param target
                $this->serviceLocator = $e->getTarget()->getServiceLocator();

                // Getting the Datas from the Event Parameters
                $params = $e->getParams();

                $viewVariables = $params['view']->getVariables();

                if ($params['view']->getTemplate() == 'MelisDemoCommerce/plugin/product-price-range')
                {
                    $viewVariables['filterMenuPriceValue'] = $this->processProductPrice($viewVariables['filterMenuPriceValue']);
                }
            },
            100);

        $this->listeners[] = $callBackHandler;
    }

    private function processProductPrice($filterMenuPriceValue)
    {
        $req = $this->serviceLocator->get('request');
        $fromFilter = $req->getQuery('m_box_product_price_max');

        // Retrieving the default Values for Product prices
        $proService = $this->serviceLocator->get('MelisComProductService');
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

    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }
}