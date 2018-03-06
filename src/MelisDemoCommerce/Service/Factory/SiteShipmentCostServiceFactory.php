<?php

/**
 * Melis Technology (http://www.melistechnology.com)
*
* @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
*
*/

namespace MelisDemoCommerce\Service\Factory;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;
use MelisDemoCommerce\Service\SiteShipmentCostService;

/**
 * SiteShipmentCost Services Factory
 */
class SiteShipmentCostServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $sl)
    {
        $siteShipmentCostService = new SiteShipmentCostService();
        $siteShipmentCostService->setServiceLocator($sl);
        return $siteShipmentCostService;
    }
}