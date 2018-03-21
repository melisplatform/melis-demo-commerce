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
use Zend\View\Model\ViewModel;

class SiteFakePaypalStyleListener implements ListenerAggregateInterface
{
    private $serviceLocator;
	
    public function attach(EventManagerInterface $events)
    {
        $sharedEvents      = $events->getSharedManager();
        
        $callBackHandler = $sharedEvents->attach(
        	'*',
            array(
                'meliscommerce_checkout_plugin_payment',
            ),
        	function($e){
        	    // Getting the Service Locator from param target
        	    $this->serviceLocator = $e->getTarget()->getServiceLocator();
        	    // Getting the Datas from the Event Parameters
        	    $params = $e->getParams();
        	    
        	    $viewModel = new ViewModel();
        	    $viewModel->setTemplate('MelisDemoCommerce/plugin/checkout-fake-paypal-style');
        	    
        	    // Getting the Site config "MelisDemoCommerce.config.php"
        	    $siteConfig = $this->serviceLocator->get('config');
        	    $siteConfig = $siteConfig['site']['MelisDemoCommerce'];
        	    $siteDatas = $siteConfig['datas'];
        	    
        	    // Generating the Product Remove link using MelisEngineTree Service
        	    $melisTree = $this->serviceLocator->get('MelisEngineTree');
        	    $queryData = array(
	                'm_checkout_step' => 'checkout-confirm',
	                'm_conf_order_id' => $params['orderDetails']['orderId']
	            );
        	    $checkoutConfirmPage = $melisTree->getPageLink($siteDatas['checkout_page_id'], true).'?'.http_build_query($queryData);
        	    
        	    /**
        	     * This process will display a fake payment form
        	     * and handle all data that need to process the Order
        	     */
        	    $config = $this->serviceLocator->get('config');
        	    $appConfigForm = $config['plugins']['MelisDemoCommerce']['forms']['MelisDemoCommerce_checkout_fake_paypal_form'];
        	    
        	    $factory = new \Zend\Form\Factory();
        	    $formElements = $this->serviceLocator->get('FormElementManager');
        	    $factory->setFormElementManager($formElements);
        	    $paymentForm = $factory->createForm($appConfigForm);
        	    
    	        $data = array(
    	            // Order id related to commerce table "melis_ecom_order"
    	            'order-id' => $params['orderDetails']['orderId'],
    	            // Country Id of the site, this will identify which country the stock or price taken for product(s)
    	            'payment-transaction-country-id' => $params['orderDetails']['countryId'],
    	            // Coupon Id, if specified the checkout has a validated coupon for Discount
    	            'payment-transaction-coupon-id' => $params['orderDetails']['couponId'],
    	            // The Total cost of the Cart/Basket
    	            'payment-transaction-total-cost' => $params['orderDetails']['totalCost'],
    	            // Confirm page
    	            'checkout-confirm-url' => $checkoutConfirmPage
    	        );
    	        
    	        $request = $this->serviceLocator->get('Request');
    	        $getData = get_object_vars($request->getQuery());
    	        
    	        $e->getTarget()->getController()->layout()->setVariables(array(
    	            'pageJs' => array(
    	                '/MelisDemoCommerce/js/melisSiteHelper.js',
    	                '/MelisDemoCommerce/js/checkout-paypal-style.js',
    	            ),
    	        ));
    	        
    	        $paymentForm->setData($data);
        	    $viewModel->setVariable('fakePaymentForm', $paymentForm);
        	    $viewModel->setVariable('hasErr', (!empty($getData['payment_err'])) ? 1 : 0);
        	    $params['chechout'] = $viewModel;
        	},
        0);
        
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