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
use Zend\Stdlib\ArrayUtils;
class ComLostPasswordController extends BaseController
{
    public function indexAction()
    {
        /**
         * Getting Demo Site config for pages redirection
         */
        $siteConfig = $this->getServiceLocator()->get('config');
        $siteConfig = $siteConfig['site']['MelisDemoCommerce'];
        $siteDatas = $siteConfig['datas'];
        
        // Generating the Redirect link using MelisEngineTree Service
        $melisTree = $this->getServiceLocator()->get('MelisEngineTree');
        
        // Login page
        $loginPage = $melisTree->getPageLink($siteDatas['login_regestration_page_id']);
        
        $recoveryKey = $this->params()->fromQuery('m_recovery_key');
        if (!$recoveryKey)
        {
            // check if the recovery key exists
            $lostPasswordResetPageId = $siteDatas['lost_password_reset_page_id'];
            // Generating the Redirect link using MelisEngineTree Service
            $redirect_link = $melisTree->getPageLink($lostPasswordResetPageId, true);
            $lostPasswordPlugin = $this->MelisCommerceLostPasswordGetEmailPlugin();
            
            $lostPasswordParameter = array(
                'lost_password_reset_page_link' => $redirect_link,
                'template_path' => 'MelisDemoCommerce/plugin/lost-password',
            );
            // add generated view to children views for displaying it in the contact view
            $this->view->addChild($lostPasswordPlugin->render($lostPasswordParameter), 'lostPassword_lostPasswordReset');
        }
        else 
        {
            // Account page
            $profilePage = $melisTree->getPageLink($siteDatas['account_page_id']);
            
            $lostPasswordResetPlugin = $this->MelisCommerceLostPasswordResetPlugin();
            $lostPasswordResetParameter = array(
                'm_autologin' => true,
                'm_recovery_key' => $recoveryKey,
                'm_redirection_link_ok' => $profilePage,
                'template_path' => 'MelisDemoCommerce/plugin/reset-password',
            );
            // add generated view to children views for displaying it in the contact view
            $this->view->addChild($lostPasswordResetPlugin->render($lostPasswordResetParameter), 'lostPassword_lostPasswordReset');
        }
        
        $this->layout()->setVariables(array(
            'pageJs' => array(
                '/MelisDemoCommerce/js/melisSiteHelper.js',
                '/MelisDemoCommerce/js/lost-passsword-recovery.js',
            ),
        ));
        
        $this->view->setVariable('idPage', $this->idPage);
        return $this->view;
    }

    public function submitLostPasswordAction()
    {
        $success = 0;
        $errors  = 0;
        $message = '';
        $request = $this->getRequest();
        
        if($request->isPost()) 
        {
            
            $siteConfig = $this->getServiceLocator()->get('config');
            $siteConfig = $siteConfig['site']['MelisDemoCommerce'];
            $siteDatas = $siteConfig['datas'];
            
            $data = array(
                'email' => $siteConfig['datas']['lostpassword_email'],
            );
            
            $emailConfig = ArrayUtils::merge(get_object_vars($request->getPost()), $data);
            
            $lostPasswordResetPlugin = $this->MelisCommerceLostPasswordGetEmailPlugin();
            $result = $lostPasswordResetPlugin->render($emailConfig)->getVariables();
            
            // Retrieving view variable from view
            $success = $result->success;
            $message = $result->message;
            $errors = $result->errors;
        }
        
        $response = array(
            'success' => $success,
            'errors'  => $errors,
            'message' => $message
        );
        
        return new JsonModel($response);
    }

    public function submitResetPasswordAction()
    {
        $success = 0;
        $errors  = 0;
        $message = '';
        $request = $this->getRequest();
        
        if($request->isPost()) 
        {
            $lostPasswordResetPlugin = $this->MelisCommerceLostPasswordResetPlugin();
            $result = $lostPasswordResetPlugin->render(get_object_vars($request->getPost()))->getVariables();
            
            // Retrieving view variable from view
            $success = $result->success;
            $message = $result->message;
            $errors  = $result->errors;
            $redirectUrl  = $result->m_redirection_link_ok;
        }
        
        $response = array(
            'success' => $success,
            'errors'  => $errors,
            'message' => $message,
            'redirectUrl' => $redirectUrl
        );
        
        return new JsonModel($response);
    }
}