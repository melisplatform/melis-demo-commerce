<?php 

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisDemoCommerce\Listener;

use Laminas\EventManager\EventManagerInterface;
use Laminas\View\Model\ViewModel;

class SiteFakePaymentListener extends SiteGeneralListener
{
	private $serviceManager;
	
	public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->attachEventListener(
            $events,
			'*',
			[
				'meliscommerce_checkout_plugin_payment',
			],
			function($e){
				// Getting the Service Manager from param target
				$this->serviceManager = $e->getTarget()->getServiceManager();
				// Getting the Datas from the Event Parameters
				$params = $e->getParams();
				
				$viewModel = new ViewModel();
				$viewModel->setTemplate('MelisDemoCommerce/plugin/checkout-fake-payment');
				
				/**
				 * This process will display a fake payment form
				 * and handle all data that need to process the Order
				 */
				$config = $this->serviceManager->get('config');
				$appConfigForm = $config['plugins']['MelisDemoCommerce']['forms']['MelisDemoCommerce_checkout_fake_payment_form'];
				
				$factory = new \Laminas\Form\Factory();
				$formElements = $this->serviceManager->get('FormElementManager');
				$factory->setFormElementManager($formElements);
				$paymentForm = $factory->createForm($appConfigForm);
				
				$data = array(
					// Order id related to commerce table "melis_ecom_order"
					'order-id' => $params['orderDetails']['orderId'],
					// The Total cost of the Cart/Basket
					'payment-transaction-total-cost' => $params['orderDetails']['totalCost'],
				);
				
				$request = $this->serviceManager->get('Request');
				$getData = get_object_vars($request->getQuery());
				
				$paymentForm->setData($data);
				$viewModel->setVariable('fakePaymentForm', $paymentForm);
				$viewModel->setVariable('hasErr', (!empty($getData['payment_err'])) ? 1 : 0);
				$params['chechout'] = $viewModel;
			},
			100
		);
	}
}