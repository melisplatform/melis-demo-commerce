<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2015 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisDemoCommerce\Controller;

use MelisFront\Service\MelisSiteConfigService;
use Laminas\View\Model\JsonModel;
use Laminas\Stdlib\ArrayUtils;

class ComLostPasswordController extends BaseController
{
    public function indexAction()
    {
        /** @var MelisSiteConfigService $siteConfigSrv */
        $siteConfigSrv = $this->getServiceManager()->get('MelisSiteConfigService');

        // Generating the Redirect link using MelisEngineTree Service
        $melisTree = $this->getServiceManager()->get('MelisEngineTree');
        
        // Login page
        $loginPage = $melisTree->getPageLink($siteConfigSrv->getSiteConfigByKey('login_regestration_page_id', $this->idPage));

        $recoveryKey = $this->params()->fromQuery('m_recovery_key');
        if (!$recoveryKey)
        {
            // check if the recovery key exists
            $lostPasswordResetPageId = $siteConfigSrv->getSiteConfigByKey('lost_password_reset_page_id', $this->idPage);
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
            $profilePage = $melisTree->getPageLink($siteConfigSrv->getSiteConfigByKey('account_page_id', $this->idPage));

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
        
        $this->view->setVariable('idPage', $this->idPage);
        return $this->view;
    }

    public function submitLostPasswordAction()
    {

        /** @var MelisSiteConfigService $siteConfigSrv */
        $siteConfigSrv = $this->getServiceManager()->get('MelisSiteConfigService');

        $success = 0;
        $errors  = 0;
        $message = '';
        $request = $this->getRequest();
        
        if($request->isPost()) 
        {
            $pageId = $this->params()->fromPost('idPage');

            $data = array(
                'email' => $siteConfigSrv->getSiteConfigByKey('lostpassword_email', $pageId),
            );
            
            $emailConfig = ArrayUtils::merge($request->getPost()->toArray(), $data);
            
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
            $result = $lostPasswordResetPlugin->render($request->getPost()->toArray())->getVariables();
            
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