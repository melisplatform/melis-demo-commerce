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
        
        $accountPageId = $siteDatas['account_page_id'];
        $accountPage = $melisTree->getPageLink($accountPageId, true);
        
        $loginPageId = $siteDatas['login_regestration_page_id'];
        $loginPage = $melisTree->getPageLink($loginPageId, true);
        
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
                'redirection_link_is_loggedin' => $accountPage,
                'template_path' => 'MelisDemoCommerce/plugin/lost-password',
            );
            // add generated view to children views for displaying it in the contact view
            $this->view->addChild($lostPasswordPlugin->render($lostPasswordParameter), 'lostPassword_lostPasswordReset');
        }
        else 
        {
                $lostPasswordResetPlugin = $this->MelisCommerceLostPasswordResetPlugin();
                $lostPasswordResetParameter = array(
                    'redirect_link_loggedin' => $accountPage,
                    'redirect_link_not_loggedin' => $loginPage,
                    'm_autologin' => true,
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

        if($request->isPost()) {
            
            $siteConfig = $this->getServiceLocator()->get('config');
            $siteConfig = $siteConfig['site']['MelisDemoCommerce'];
            $siteDatas = $siteConfig['datas'];
            
            $lostPasswordResetPlugin = $this->MelisCommerceLostPasswordGetEmailPlugin();
            
            $requestVar = get_object_vars($this->getRequest()->getPost());
            
            $data = array(
                'm_is_submit' => true,
                'lost_password_reset_page_link' => 'http://'.$_SERVER['SERVER_NAME'].'/lost-password/id/64',
                'redirection_link_is_loggedin'  => 'http://'.$_SERVER['SERVER_NAME'].'/lost-password/id/64',
                'email' => $siteConfig['datas']['lostpassword_email'],
            );
            
            // overide email default values
            $data['email']['email_to'] = $requestVar['m_email'];
            $data['email']['email_content_tag_replace'] = array('domain' => $request->getUri()->getHost());
            
            $result = $lostPasswordResetPlugin->render($data)->getVariables();

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

        if($request->isPost()) {
            $lostPasswordResetPlugin = $this->MelisCommerceLostPasswordResetPlugin();
            $result = $lostPasswordResetPlugin->render(array('m_is_submit' => true))->getVariables();

            // Retrieving view variable from view
            $success = $result->success;
            $message = $result->message;
            $errors  = $result->errors;
        }

        $response = array(
            'success' => $success,
            'errors'  => $errors,
            'message' => $message
        );

        return new JsonModel($response);
    }
}