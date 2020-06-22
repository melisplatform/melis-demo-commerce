<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2015 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisDemoCommerce\Controller;

use Laminas\View\Model\JsonModel;

class ComOrderController extends BaseController
{
    public function indexAction()
    {
        $orderPlugin = $this->MelisCommerceOrderPlugin();
        $orderParameter = array(
            'template_path' => 'MelisDemoCommerce/order-details',
            'order_address_parameters' => array(
                'template_path' => 'MelisDemoCommerce/order-addresses'
            ),
            'order_shipping_details_parameters' => array(
                'template_path' => 'MelisDemocommerce/order-shipping-details'
            ),
            'order_messages_parameters' => array(
                'template_path' => 'MelisDemocommerce/order-messages'
            ),
        );
        $this->view->addChild($orderPlugin->render($orderParameter), 'showOrderDetails');
        
        $this->layout()->setVariables(array(
            'pageJs' => array(
                '/MelisDemoCommerce/js/melisSiteHelper.js',
                '/MelisDemoCommerce/js/order-details.js',
            ),
        ));
        
        $this->view->setVariable('idPage', $this->idPage);
        return $this->view;
    }
    
    public function submitOrderMessageAction()
    {
        $result = array();
        $orderMessagePlugin = $this->MelisCommerceOrderMessagesPlugin();
        $orderMessageParameter = array();
        
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $result = $orderMessagePlugin->render($orderMessageParameter)->getVariables();
        }
        return new JsonModel($result);
    }
    
}