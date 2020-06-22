<?php 

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisDemoCommerce\Listener;

use MelisFront\Service\MelisSiteConfigService;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Stdlib\Parameters;
use Laminas\Http\Request;
use Laminas\Session\Container;
class SiteFakePaymentProcesstListener extends SiteGeneralListener
{
	private $serviceManager;
	
	public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->attachEventListener(
            $events,
			'MelisDemoCommerce',
			[
				'meliscommerce_checkout_postpayment_confirmation',
			],
			function($e){
				// Getting the Service Locator from param target
				$this->serviceManager = $e->getTarget()->getServiceManager();
				// Getting the Datas from the Event Parameters
				$params = $e->getParams();
				
				$request = $this->serviceManager->get('Request');
				$data = get_object_vars($request->getQuery());

				/** @var MelisSiteConfigService $siteConfigSrv */
				$siteConfigSrv = $this->serviceManager->get('MelisSiteConfigService');
				$siteId = $siteConfigSrv->getSiteConfigByKey('site_id', $params['idPage']);

				// Generating the Product Remove link using MelisEngineTree Service
				$melisTree = $this->serviceManager->get('MelisEngineTree');
				$checkoutPage = $melisTree->getPageLink($siteConfigSrv->getSiteConfigByKey('checkout_page_id', $params['idPage']), false);

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
					
					$this->serviceManager->get('request')->setPost($postParam);
					
					$orderCheckoutService = $this->serviceManager->get('MelisComOrderCheckoutService');
					$orderCheckoutService->setSiteId($siteId);
					$orderCheckoutService->checkoutStep2_postPayment();
					
					$container = new Container('meliscommerce');
					$queryData = array(
						'm_checkout_step' => 'checkout-confirm',
						'm_conf_order_id' => $container['checkout'][$siteId]['orderId']
					);
					
					// Unsetting Site Checkout Session
					unset($container['checkout'][$siteId]);
					
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
			100
		);
	}
}