<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2015 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisDemoCommerce\Controller;

use MelisFront\Controller\MelisSiteActionController;
use MelisFront\Service\MelisSiteConfigService;
use Zend\Session\Container;

class ComLogoutController extends BaseController
{
    public function logoutAction()
    {
        /** @var MelisSiteConfigService $siteConfigSrv */
        $siteConfigSrv = $this->getServiceLocator()->get('MelisSiteConfigService');
        // Clear user Session using Commerce Authentication Service
        $melisComAuthSrv = $this->getServiceLocator()->get('MelisComAuthenticationService');
        $melisComAuthSrv->logout();

        //get page id from query
        $pageId = $this->params()->fromQuery('pageId');

        $siteId = $siteConfigSrv->getSiteConfigByKey('site_id', $pageId);
        
        // Clearing checkout sessions
        $container = new Container('meliscommerce');
        if (!empty($container['checkout']))
        {
            if (!empty($container['checkout'][$siteId]))
            {
                unset($container['checkout'][$siteId]);
            }
        }

        /**
         * Getting the Logout redirect page id from site config
         */
        $logoutRedirectPageId = $siteConfigSrv->getSiteConfigByKey('logout_redirect_page_id', $pageId);
        // Generating the Redirect link using MelisEngineTree Service
        $melisTree = $this->getServiceLocator()->get('MelisEngineTree');
        $redirect_link = $melisTree->getPageLink($logoutRedirectPageId, true);

        return $this->redirect()->toUrl($redirect_link);
    }
}