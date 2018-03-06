<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2015 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisDemoCommerce\Controller;

use MelisDemoCommerce\Controller\BaseController;
use Zend\View\Model\JsonModel;
use Zend\Config\Reader\Json;
use Zend\Stdlib\ArrayUtils;

class ComCheckoutController extends BaseController
{
    /**
     * This page will process the Checkout of the Client
     * to become an Order record
     */
    public function checkoutAction()
    {
        // Getting the Site config "MelisDemoCommerce.config.php"
        $siteConfig = $this->getServiceLocator()->get('config');
        $siteConfig = $siteConfig['site']['MelisDemoCommerce'];
        $siteDatas = $siteConfig['datas'];
        
        $countryId = $siteDatas['site_country_id'];
        $siteId = $siteDatas['site_id'];
        $loginPageId = $siteDatas['login_regestration_page_id'];
        
        // Generating the Product Remove link using MelisEngineTree Service
        $melisTree = $this->getServiceLocator()->get('MelisEngineTree');
        
        $checkoutPageLink = $melisTree->getPageLink($this->idPage, false);
        $loginPage = $melisTree->getPageLink($loginPageId, false);
        
        /**
         * MelisCommerceCheckoutPlugin this Plugin process the Checkout
         * This pulig provides pages with specific task as follows
         * 1. Cart - the cart of the Client/user
         * 2. Addresses - Delivery and Billing address for checkout
         * 3. Summary - this page will show the summary of the checkout
         * 3. Comfirm summary - This page will validate the checkout to become Order, 
         *                      this will display a page if the process encounter an
         *                      error(s), otherwire this will provide a url for payment page
         *                      where the payment of the checkout process.
         *                      In this Demo we created a FAKE payment form in-order to
         *                      proceed the checkout.
         * 4. Payment - the step required event listener to process Payment
         * 5. Order confirmation - the Order confirmation page of the checkout
         */
        $checkoutPlugin = $this->MelisCommerceCheckoutPlugin();
        $checkoutPluginParameters = array(
            'template_path' => 'MelisDemoCommerce/plugin/check-out',
            'checkout_cart_parameters' => array(
                'template_path' => 'MelisDemoCommerce/plugin/check-out-cart',
                'checkout_cart_coupon_parameters' => array(
                    'template_path' => 'MelisDemoCommerce/plugin/check-out-cart-coupon',
                ),
            ),
            'checkout_addresses_parameters' => array(
                'template_path' => 'MelisDemoCommerce/plugin/check-out-addresses',
            ),
            'checkout_summary_parameters' => array(
                'template_path' => 'MelisDemoCommerce/plugin/check-out-summary',
            ),
            'checkout_confirm_summary_parameters' => array(
                'template_path' => 'MelisDemoCommerce/plugin/check-out-confirm-summary',
            ),
            'checkout_confirm_parameters' => array(
                'template_path' => 'MelisDemoCommerce/plugin/check-out-confirmation',
                'address_parameters' => array(
                    'template_path' => 'MelisDemoCommerce/show-order-addresses',
                ),
                'shipping_parameters' => array(
                    'template_path' => 'MelisDemocommerce/show-shipping-details',
                ),
                'm_c_order' => $this->params()->fromQuery('m_c_order',''), // Order id use in Confirmation Page after Payments
                'm_image_type' => $siteDatas['image_preview'],
                'm_custom_image' => $siteDatas['default_image'],
            ),
            'm_checkout_country_id' => $countryId,
            'm_checkout_site_id' => $siteId,
            'm_login_page_id' => $loginPageId,
            'm_checkout_page_link' => $checkoutPageLink,
            'm_login_page_link' => $loginPage,
        );
        $checkoutPluginView = $checkoutPlugin->render($checkoutPluginParameters);
        
        $this->view->addChild($checkoutPluginView, 'checkOut');
        
        $layoutvar = $this->layout()->getVariable('pageJs');
        $layoutvar[] = '/MelisDemoCommerce/js/checkout.js';
        $this->layout()->setVariables(array(
            'pageJs' => $layoutvar,
        ));
        
        $this->view->setVariable('idPage', $this->idPage);
        return $this->view;
    }
    
    public function confirmPaymentAction()
    {
        // Confirm Payment Listener
        $this->getEventManager()->trigger('meliscommerce_checkout_postpayment_confirmation', $this, array());
        return new JsonModel(array());
    }
    
    /**
     * This method retrieving the address details of a
     * client logged in
     * @return Json
     */
    public function getSelectedAddressDetailsAction()
    {
        // Default Values
        $status  = 0;
        $address  = array();
         
        $request = $this->getRequest();
        
        if ($request->isPost())
        {
            $postData = get_object_vars($request->getPost());
            
            $accountPlugin = $this->MelisCommerceAccountPlugin();
            $data = $accountPlugin->getSelectedAddressDetailsAction($postData['cadd_id']);
            
            if (!empty($data))
            {
                $status = 1;
                foreach ($data As $key => $val)
                {
                    $address[str_replace('cadd_', 'm_add_'.$postData['type'].'_', $key)] = $val;
                }
            }
        }
        
        $response = array(
            'success' => $status,
            'address' => $address,
        );
         
        return new JsonModel($response);
    }
}