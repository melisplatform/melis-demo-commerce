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

class SiteFakePaymentListener implements ListenerAggregateInterface
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
        	    $viewModel->setTemplate('MelisDemoCommerce/plugin/checkout-fake-payment');
        	    
        	    /**
        	     * This process will display a fake payment form
        	     * and handle all data that need to process the Order
        	     */
        	    $config = $this->serviceLocator->get('config');
        	    $appConfigForm = $config['plugins']['MelisDemoCommerce']['forms']['MelisDemoCommerce_checkout_fake_payment_form'];
        	    
        	    $factory = new \Zend\Form\Factory();
        	    $formElements = $this->serviceLocator->get('FormElementManager');
        	    $factory->setFormElementManager($formElements);
        	    $paymentForm = $factory->createForm($appConfigForm);
        	    
    	        $data = array(
    	            // Order id related to commerce table "melis_ecom_order"
    	            'order-id' => $params['orderDetails']['orderId'],
    	            // The Total cost of the Cart/Basket
    	            'payment-transaction-total-cost' => $params['orderDetails']['totalCost'],
    	        );
    	        
    	        $request = $this->serviceLocator->get('Request');
    	        $getData = get_object_vars($request->getQuery());
    	        
    	        $paymentForm->setData($data);
        	    $viewModel->setVariable('fakePaymentForm', $paymentForm);
        	    $viewModel->setVariable('hasErr', (!empty($getData['payment_err'])) ? 1 : 0);
        	    $params['chechout'] = $viewModel;
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