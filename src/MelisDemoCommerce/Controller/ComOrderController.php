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

class ComOrderController extends BaseController
{
    public function indexAction()
    {
        /*
         *  Getting site config 
         */
        $siteConfig = $this->getServiceLocator()->get('config');
        $siteConfig = $siteConfig['site']['MelisDemoCommerce'];
        $siteDatas = $siteConfig['datas'];
        $dummyData = array();
        $orderPlugin = $this->MelisCommerceOrderPlugin();
        $orderParameter = array(
            'template_path' => 'MelisDemoCommerce/show-order-details',
            'm_basket_image' => $siteDatas['image_preview'],
            'm_image_src' =>  $siteDatas['image_src'],
            'address_template_path' => 'MelisDemoCommerce/show-order-addresses',
            'shipping_template_path' => 'MelisDemocommerce/show-shipping-details',
            'message_template_path' => 'MelisDemocommerce/show-order-messages',
        );
        $this->view->addChild($orderPlugin->render($orderParameter), 'showOrderDetails');
        
        $this->layout()->setVariables(array(
            'pageJs' => array(
                '/MelisDemoCommerce/js/melisSiteHelper.js',
                '/MelisDemoCommerce/js/order-details.js',
            ),
        ));
        
        // add dummy data for Back Office viewing
        if($this->renderMode == 'melis'){
           $dummyData = (!empty($siteDatas['dummy_order_details']) ? $siteDatas['dummy_order_details'] : '');
        }
        
        $this->view->setVariable('idPage', $this->idPage);
        $this->layout()->setVariable('dummyOrder', $dummyData);
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