<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2015 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisDemoCommerce\Controller;

use MelisFront\Controller\MelisSiteActionController;
use Zend\Session\Container;

class ComLogoutController extends MelisSiteActionController
{
    public function logoutAction()
    {   
        // Clear user Session using Commerce Authentication Service
        $melisComAuthSrv = $this->getServiceLocator()->get('MelisComAuthenticationService');
        $melisComAuthSrv->logout();
        
        // Getting the Site config "MelisDemoCommerce.config.php"
        $siteConfig = $this->getServiceLocator()->get('config');
        $siteConfig = $siteConfig['site']['MelisDemoCommerce'];
        $siteDatas = $siteConfig['datas'];
        $siteId = $siteDatas['site_id'];
        
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
        $siteConfig = $this->getServiceLocator()->get('config');
        $siteConfig = $siteConfig['site']['MelisDemoCommerce'];
        $siteDatas = $siteConfig['datas'];
        $logoutRedirectPageId = $siteDatas['logout_redirect_page_id'];
        // Generating the Redirect link using MelisEngineTree Service
        $melisTree = $this->getServiceLocator()->get('MelisEngineTree');
        $redirect_link = $melisTree->getPageLink($logoutRedirectPageId, true);
        return $this->redirect()->toUrl($redirect_link);
    }
}