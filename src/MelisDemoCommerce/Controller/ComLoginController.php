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

class ComLoginController extends BaseController
{
    public function indexAction()
    {
        /**
         * Getting the Account page id as Page redirected
         * after login Authentication success
         */
        $siteConfig = $this->getServiceLocator()->get('config');
        $siteConfig = $siteConfig['site']['MelisDemoCommerce'];
        $siteDatas = $siteConfig['datas'];
        $accountPageId = $siteDatas['account_page_id'];
        // Generating the Redirect link using MelisEngineTree Service
        $melisTree = $this->getServiceLocator()->get('MelisEngineTree');
        $redirect_link = $melisTree->getPageLink($accountPageId);
        
        /**
         * Checking if the user is logged in else
         * this will redirect to account page
         */
        $melisComAuthSrv = $this->getServiceLocator()->get('MelisComAuthenticationService');
        
        // only redirect if rendering is front
        if($this->renderMode == 'front')
        {
            if ($melisComAuthSrv->hasIdentity())
            {
                $this->redirect()->toUrl($redirect_link);
            }
        }
        
        /**
         * Generating Login form using MelisCommerceRegisterPlugin Plugin
         */
        $login = $this->MelisCommerceLoginPlugin();
        // Adding the custom link the user is already identify
        $loginParameters = array(
            'm_redirection_link_ok' => $redirect_link,
            'template_path' => 'MelisDemoCommerce/plugin/login',
        );
        // add generated view to children views for displaying it in the contact view
        $this->view->addChild($login->render($loginParameters), 'login');
        
        /**
         * Generating Registration form using MelisCommerceRegisterPlugin Plugin
         */
        $registration = $this->MelisCommerceRegisterPlugin();
        $registrationParameters = array(
            'template_path' => 'MelisDemoCommerce/plugin/registration',
            'm_redirection_link_ok' => $redirect_link,
            'm_autologin' => true,
        );
        // add generated view to children views for displaying it in the contact view
        $this->view->addChild($registration->render($registrationParameters), 'registration');
        $this->view->setVariable('idPage', $this->idPage);
        return $this->view;
    }
    
    public function submitRegistrationAction()
    {
        // Default Values
        $status  = 0;
        $errors  = array();
        $redirect_link = null;
         
        $request = $this->getRequest();
        
        if ($request->isPost())
        {
            /**
             * Validating the Registration form by Calling the 
             * plugin and adding Post as parameter
             */
            $registration = $this->MelisCommerceRegisterPlugin();
            $result = $registration->render(get_object_vars($request->getPost()))->getVariables();
        
            // Retrieving view variable from view
            $status = $result->success;
            $errors = $result->errors;
            $redirect_link = $result->redirect_link;
        }
        
        $response = array(
            'success' => $status,
            'errors' => $errors,
            'redirect_link' => $redirect_link
        );
         
        return new JsonModel($response);
    }
    
    public function submitLoginAction()
    {
        // Default Values
        $status  = 0;
        $message = null;
        $errors  = array();
        $redirect_link = null;
        
        $request = $this->getRequest();
        
        if ($request->isPost())
        {
            /**
             * Validating the Registration form by Calling the
             * plugin and adding Post as parameter
             */
            $login = $this->MelisCommerceLoginPlugin();
            $result = $login->render(get_object_vars($request->getPost()))->getVariables();
            
            // Retrieving view variable from view
            $message = $result->message;
            $status = $result->success;
            $errors = $result->errors;
            $redirect_link = $result->redirect_link;
        }
        
        $response = array(
            'success' => $status,
            'message' => $message,
            'errors' => $errors,
            'redirect_link' => $redirect_link
        );
        
        return new JsonModel($response);
    }
}
