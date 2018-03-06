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
use MelisDemoCommerce\Service\DemoCommerceService;

/**
 * MelisDemoCommerce Services Factory
 */
class DemoCommerceServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $sl)
    {
        $demoCommeceService = new DemoCommerceService();
        $demoCommeceService->setServiceLocator($sl);
        return $demoCommeceService;
    }
}