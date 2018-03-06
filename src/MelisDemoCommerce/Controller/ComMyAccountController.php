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
class ComMyAccountController extends BaseController
{
    public function indexAction()
    {
        /**
         * Getting the Login page id as Page redirected
         * if the user doesn't have authenticated yet
         */
        $siteConfig = $this->getServiceLocator()->get('config');
        $siteConfig = $siteConfig['site']['MelisDemoCommerce'];
        $siteDatas = $siteConfig['datas'];
        $loginPageId = $siteDatas['login_regestration_page_id'];
        // Generating the Redirect link using MelisEngineTree Service
        $melisTree = $this->getServiceLocator()->get('MelisEngineTree');
        $redirect_link = $melisTree->getPageLink($loginPageId, true);
        
        /**
         * Checking if the user is logged in else
         * this will redirect to login page
         */
        $melisComAuthSrv = $this->getServiceLocator()->get('MelisComAuthenticationService');
        // only redirect if rendering is front
        if($this->renderMode == 'front'){
            if (!$melisComAuthSrv->hasIdentity())
            {
                $this->redirect()->toUrl($redirect_link);
            }
        }
        
        $clientAccount = $this->MelisCommerceAccountPlugin();
        $clientProfileParam = array(
            'template_path' => 'MelisDemoCommerce/plugin/account',
            'profile_parameter' => array(
                'template_path' => 'MelisDemoCommerce/plugin/profile'
            ),
            'delivery_parameter' => array(
                'template_path' => 'MelisDemoCommerce/plugin/delivery-address',
                'show_select_address_data' => true,
            ),
            'billing_parameter' => array(
                'template_path' => 'MelisDemoCommerce/plugin/billing-address',
                'show_select_address_data' => true,
            ),
            'my_cart_parameter' => array(
                'template_path' => 'MelisDemoCommerce/plugin/my-cart',
            ),
            'order_history_paremeter' => array(
                'template_path' => 'MelisDemoCommerce/plugin/order-history',  
            ),
        );
        $this->view->addChild($clientAccount->render($clientProfileParam), 'account');
        
        $this->layout()->setVariables(array(
            'pageJs' => array(
                '/MelisDemoCommerce/js/melisSiteHelper.js',
                '/MelisDemoCommerce/js/account.js',
            ),
        ));
        
        $this->view->setVariable('idPage', $this->idPage);
        return $this->view;
    }
    
    public function submitAction()
    {
        // Default Values
        $status  = 0;
        $errors  = array();
         
        $request = $this->getRequest();
        
        if ($request->isPost())
        {
            $formType = $this->params()->fromQuery('form-type');
            
            $clientAccount = $this->MelisCommerceAccountPlugin();
            $result = $clientAccount->render()->getVariables();
            
            $temp = $formType.'_variables';
            // Retrieving view variable from view
            $status = $result->$temp->success;
            $errors = $result->$temp->errors;
        }
        
        $response = array(
            'success' => $status,
            'errors' => $errors,
        );
         
        return new JsonModel($response);
    }

    public function getAddressAction()
    {
        $success = 0;
        $address = [];
        $request = $this->getRequest();
        
        if($request->isPost()) {
            $data       = get_object_vars($request->getPost());
            $addressId  = (int) $data['id'];
            
            $accountPlugin = $this->MelisCommerceAccountPlugin();
            $data = $accountPlugin->getSelectedAddressDetailsAction($addressId);
            if (!empty($data))
            {
                $address = $data;
                $success = 1;
            }
        }
        
        $response = [
            'success'  => $success,
            'address'  => $address,
        ];
        
        return new JsonModel($response);
    }

    public function deleteAddressAction()
    {
        $success = 0;
        $message = 'Unable to delete address';
        $request = $this->getRequest();
        
        if($request->isPost()) {
            $data       = get_object_vars($request->getPost());
            $addressId  = (int) $data['id'];
            
            $accountPlugin = $this->MelisCommerceAccountPlugin();
            $success = $accountPlugin->deleteAddressAction($addressId);
            
            if($success) {
                $success = 1;
            }
        }
        
        $response = [
            'success'  => $success,
            'message'  => $message,
        ];
        
        return new JsonModel($response);
    }
    
    public function getUserNameAction()
    {
        $personName = null;
        $melisComAuthSrv = $this->getServiceLocator()->get('MelisComAuthenticationService');
        /**
         * Preparing the header link of the page
         * If user has been logged in the $personName will return
         * the name of the person logged in with frist name and last name
         */
        if ($melisComAuthSrv->hasIdentity())
        {
            $personName = $melisComAuthSrv->getPersonName();
        }
        
        return  new JsonModel(array('personName' => $personName));
    }
}