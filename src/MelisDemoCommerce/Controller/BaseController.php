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
use Zend\View\Model\ViewModel;
use Zend\Mvc\MvcEvent;

class BaseController extends MelisSiteActionController
{
    public $view = null;
    
    function __construct()
    {
        $this->view = new ViewModel();
        set_time_limit (1000);
    }
    
    public function onDispatch(MvcEvent $event)
    {
        $sm = $event->getApplication()->getServiceManager();

        $pageId = $this->params()->fromRoute('idpage', $this->params()->fromPost('idpage'));
        $renderMode = $this->params()->fromRoute('renderMode');

        $melisComAuthSrv = $sm->get('MelisComAuthenticationService');
        $personName = null;
        $isLoggedin = false;

        /** @var MelisSiteConfigService $siteConfigSrv */
        $siteConfigSrv = $sm->get('MelisSiteConfigService');

        /**
         * Preparing the header link of the page
         * If user has been logged in the $personName will return 
         * the name of the person logged in with frist name and last name
         */
        if ($melisComAuthSrv->hasIdentity())
        {
            $isLoggedin = true;
            $personName = $melisComAuthSrv->getPersonName();
        }
        
        $this->layout()->setVariable('personName', $personName);
        $this->layout()->setVariable('isLoggedin', $isLoggedin);
    
        /**
         * Generating Site Menu using MelisFrontMenuPlugin Plugin
         */
        $menuPlugin = $this->MelisFrontMenuPlugin();
        $menuParameters = array(
            'template_path' => 'MelisDemoCommerce/plugin/menu',
            'pageIdRootMenu' => $siteConfigSrv->getSiteConfigByKey('homePageId', $pageId),
            'pageId' => $pageId
        );

        // add generated view to children views for displaying it in the contact view
        $menu = $menuPlugin->render($menuParameters);
        $this->layout()->addChild($menu, 'siteMenu');
    
        /**
         * Generating Page Breadcrumb using MelisFrontBreadcrumbPlugin Plugin
         * @var unknown $breadcrumbPlugin
         */
        $breadcrumbPlugin = $this->MelisFrontBreadcrumbPlugin();
        $breadcrumbParameters = array(
            'template_path' => 'MelisDemoCommerce/plugin/breadcrumb',
            'pageIdRootBreadcrumb' => $pageId,
        );
        
        // add generated view to children views for displaying menu cart
        $cartPlugin = $this->MelisCommerceCartPlugin();
        $cartParameters = array(
            'template_path' => 'MelisDemoCommerce/plugin/menu-cart',
            'id' => 'menuCartPlugin',
            'pageId' => $pageId
        );
        $this->layout()->addChild($cartPlugin->render($cartParameters), 'menuCart');
        
        // add generated view to children views for displaying it in the contact view
        $breadcrumb = $breadcrumbPlugin->render($breadcrumbParameters);
        $this->layout()->addChild($breadcrumb, 'pageBreadcrumb');
    
        return parent::onDispatch($event);
    }
}