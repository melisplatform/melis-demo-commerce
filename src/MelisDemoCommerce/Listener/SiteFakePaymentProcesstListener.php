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
use Zend\Stdlib\Parameters;
use Zend\Http\Request;
use Zend\Session\Container;
class SiteFakePaymentProcesstListener implements ListenerAggregateInterface
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
        	    $data = get_object_vars($request->getQuery());
        	    
        	    // Getting the Site config "MelisDemoCommerce.config.php"
        	    $siteConfig = $this->serviceLocator->get('config');
        	    $siteConfig = $siteConfig['site']['MelisDemoCommerce'];
        	    $siteDatas = $siteConfig['datas'];
        	    
        	    // Generating the Product Remove link using MelisEngineTree Service
        	    $melisTree = $this->serviceLocator->get('MelisEngineTree');
        	    $checkoutPage = $melisTree->getPageLink($siteDatas['checkout_page_id'], false);
        	    
    	        // Redirect to confirm order to show the result of the payment
    	        
        	    /**
        	     * Making fake call to distant Server By using Forward
        	     * to fake Controller
        	     */
    	        $reuslt = $e->getTarget()->forward()->dispatch(
    	            'MelisDemoCommerce\Controller\FakeServerPayment', 
    	            array_merge(array('action' => 'fakeServerAnswer'), $data)
	            )->getVariables();
    	        
    	        if ($reuslt['payment-transaction-return-code'] == 'A')
    	        {
    	            /**
    	             * Modifying POST data using the result of
    	             * the fake Server Answer
    	             */
    	            $postParam = new Parameters();
    	            foreach ($reuslt As $key => $val)
    	            {
    	                $postParam->set($key, $val);
    	            }
    	            
    	            $this->serviceLocator->get('request')->setPost($postParam);
    	             
    	            $orderCheckoutService = $this->serviceLocator->get('MelisComOrderCheckoutService');
    	            $orderCheckoutService->setSiteId($siteDatas['site_id']);
    	            $orderCheckoutService->checkoutStep2_postPayment();
    	            
    	            $container = new Container('meliscommerce');
    	            $queryData = array(
    	                'm_checkout_step' => 'checkout-confirm',
    	                'm_c_order' => $container['checkout'][$siteDatas['site_id']]['orderId']
    	            );
    	            
    	            // Unsetting Site Checkout Session
    	            unset($container['checkout'][$siteDatas['site_id']]);
    	            
    	            $e->getTarget()->redirect()->tourl($checkoutPage.'?'.http_build_query($queryData));
    	        }
    	        elseif ($reuslt['payment-transaction-return-code'] == 'R') 
    	        {
    	            // Redirect to Checkout payment with error parameter
    	            // Generating Url to Chechout page with err data
    	            $dataQuery = array(
    	                'm_checkout_step' => 'checkout-payment',
    	                'payment_err' => 1,
    	            );
    	            $checkoutPageWithErr = $checkoutPage.'?'.http_build_query($dataQuery);
                    $e->getTarget()->redirect()->tourl($checkoutPageWithErr);
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