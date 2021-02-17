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
use Laminas\Http\Request;
class SiteFakePaypalStyleProcesstListener extends SiteGeneralListener
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
                $data = $request->getPost()->toArray();

                /** @var MelisSiteConfigService $siteConfigSrv */
                $siteConfigSrv = $this->serviceManager->get('MelisSiteConfigService');
                
                if ($data['payment-transaction-return-code'] == 'A')
                {
                    $orderCheckoutService = $this->serviceManager->get('MelisComOrderCheckoutService');
                    $orderCheckoutService->checkoutStep2_postPayment();
                }
            },
            100
        );
    }
}