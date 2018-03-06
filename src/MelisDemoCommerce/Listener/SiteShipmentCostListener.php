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

class SiteShipmentCostListener implements ListenerAggregateInterface
{
    private $serviceLocator;
	
    public function attach(EventManagerInterface $events)
    {
        $sharedEvents      = $events->getSharedManager();
        
        $callBackHandler = $sharedEvents->attach(
        	'*',
            array(
                'meliscommerce_service_checkout_shipment_computation_end',
                'meliscommerce_service_checkout_post_shipment_computation_end',
            ),
        	function($e){
        	    $sm = $e->getTarget()->getServiceLocator();
        	    $params = $e->getParams();
        	    
        	    $siteShipmentCostService = $sm->get('SiteShipmentCostService');
        	    $params['results'] = $siteShipmentCostService->computeShipmentCost(get_object_vars($params)['results']);
        	},
        100);
        
        $this->listeners[] = $callBackHandler;
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
