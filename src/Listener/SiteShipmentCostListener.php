<?php 

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisDemoCommerce\Listener;

use Laminas\EventManager\EventManagerInterface;

class SiteShipmentCostListener extends SiteGeneralListener
{
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->attachEventListener(
            $events,
            '*',
            [
                'meliscommerce_service_checkout_shipment_computation_end',
                'meliscommerce_service_checkout_post_shipment_computation_end',
            ],
            function($e){
                $sm = $e->getTarget()->getServiceManager();
                $params = $e->getParams();
                
                $siteShipmentCostService = $sm->get('SiteShipmentCostService');
                $params['results'] = $siteShipmentCostService->computeShipmentCost($params['results']);
            },
            100
        );
    }
}
