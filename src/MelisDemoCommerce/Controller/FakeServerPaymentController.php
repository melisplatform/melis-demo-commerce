<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2015 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisDemoCommerce\Controller;

use MelisDemoCommerce\Controller\BaseController;
use Zend\Stdlib\ArrayUtils;
use Zend\View\Model\JsonModel;

/**
 * This class represent the Payment Gateway Server
 * This class is for testing purposes only
 */
class FakeServerPaymentController extends BaseController
{
    /**
     * This method validate the payment demo form
     * generate datas as transactions and return as response
     * to SiteFakePaymentProcesstListener listener
     */
    public function fakeServerAnswerAction()
    {
        $result = array();
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
	    
	    $datas = $this->params()->fromRoute();
	    $paymentForm->setData($datas);
	    if ($paymentForm->isValid())
	    {
	        // Generating sample valid transation code
	        $randomCode = substr(str_shuffle(str_repeat("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ", 10)), 0, 10);
	        
	        $formData = $paymentForm->getData();
	        $result = array(
	            'payment-transaction-price-paid' => $formData['payment-transaction-total-cost'],
	            'payment-transaction-price-paid-confirm' => $formData['payment-transaction-total-cost'],
	            'payment-type-value' => 'VISA',
	            'payment-transaction-id' => $randomCode,
	            'payment-transaction-return-code' => 'A',
	            'payment-transaction-date' => date('Y-m-d H:i:s'),
	        );
	        
	        $result = ArrayUtils::merge($result, $formData);
	        
	        // Unset card info before send back to Shop server
	        unset($result['card-holder-name']);
	        unset($result['card-number']);
	        unset($result['cvv']);
	        unset($result['expiry-month']);
	        unset($result['expiry-year']);
	    }
	    else 
	    {
	        $result['payment-transaction-return-code'] = 'R';
	    }
	    
	    return $result;
    }
    
    /**
     * Validating the Fake Paypal style form
     * and generate return value to shop server
     */
    public function validateFakePaypalFormAction()
    {
        // Default Values
        $status  = 0;
        $result  = array();
        
        $request = $this->getRequest();
        
        if ($request->isPost())
        {
            $postData = get_object_vars($request->getPost());
            
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
            
            $paymentForm->setData($postData);
            if ($paymentForm->isValid())
            {
                // Generating sample valid transation code
                $randomCode = substr(str_shuffle(str_repeat("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ", 10)), 0, 10);
                 
                $formData = $paymentForm->getData();
                $result = array(
                    'payment-transaction-price-paid' => $formData['payment-transaction-total-cost'],
                    'payment-transaction-price-paid-confirm' => $formData['payment-transaction-total-cost'],
                    'payment-type-value' => 'VISA',
                    'payment-transaction-id' => $randomCode,
                    'payment-transaction-return-code' => 'A',
                    'payment-transaction-date' => date('Y-m-d H:i:s'),
                );
                 
                $result = ArrayUtils::merge($result, $formData);
                 
                // Unset card info before send back to Shop server
                unset($result['card-holder-name']);
                unset($result['card-number']);
                unset($result['cvv']);
                unset($result['expiry-month']);
                unset($result['expiry-year']);
                
                $status = 1;
            }
        }
        
        $response = array(
            'success' => $status,
            'result' => $result,
        );
         
        return new JsonModel($response);
    }
}