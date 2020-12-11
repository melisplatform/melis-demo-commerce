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
            'order_return_product_parameters' => array(
                'template_path' => 'MelisDemoCommerce/order-return-product'
            ),
        );
        $this->view->addChild($orderPlugin->render($orderParameter), 'showOrderDetails');
        
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

    /**
     * @return JsonModel
     */
    public function returnProductAction()
    {
        $result = array();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $postData = $request->getPost()->toArray();
            $returnProductPlugin = $this->MelisCommerceOrderReturnProductPlugin();
            $returnProductParameter = $postData;

            $result = $returnProductPlugin->render($returnProductParameter)->getVariables();
        }

        return new JsonModel($result);
    }
}