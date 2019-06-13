<?php 

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisDemoCommerce\Listener;

use MelisFront\Service\MelisSiteConfigService;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Http\Request;
class SiteFakePaypalStyleProcesstListener implements ListenerAggregateInterface
{
    private $serviceLocator;
	
    public function attach(EventManagerInterface $events)
    {
        $sharedEvents      = $events->getSharedManager();
        
        $callBackHandler = $sharedEvents->attach(
        	'MelisDemoCommerce',
            array(
                'meliscommerce_checkout_postpayment_confirmation',
            ),
        	function($e){
        	    // Getting the Service Locator from param target
        	    $this->serviceLocator = $e->getTarget()->getServiceLocator();
        	    // Getting the Datas from the Event Parameters
        	    $params = $e->getParams();
        	    
        	    $request = $this->serviceLocator->get('Request');
        	    $data = get_object_vars($request->getPost());

                /** @var MelisSiteConfigService $siteConfigSrv */
                $siteConfigSrv = $this->serviceLocator->get('MelisSiteConfigService');
        	    
    	        if ($data['payment-transaction-return-code'] == 'A')
    	        {
    	            $orderCheckoutService = $this->serviceLocator->get('MelisComOrderCheckoutService');
    	            $orderCheckoutService->setSiteId($siteConfigSrv->getSiteConfigByKey('site_id', $params['idPage']));
    	            $orderCheckoutService->checkoutStep2_postPayment();
    	        }
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