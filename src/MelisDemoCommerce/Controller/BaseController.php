<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2015 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisDemoCommerce\Controller;

use MelisFront\Controller\MelisSiteActionController;
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
        $personName = null;
        $isLoggedin = false;
        $melisComAuthSrv = $this->getServiceLocator()->get('MelisComAuthenticationService');
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
        
        // Getting the Site config "MelisDemoCommerce.config.php"
        $sm = $event->getApplication()->getServiceManager();
        $siteConfig = $sm->get('config');
        $siteConfig = $siteConfig['site']['MelisDemoCommerce'];
        $siteDatas = $siteConfig['datas'];
        // Adding the SiteDatas to layout so views can access to the SiteDatas easily
        $this->layout()->setVariable('siteDatas', $siteDatas);
    
        $pageId = $this->params()->fromRoute('idpage');
        $renderMode = $this->params()->fromRoute('renderMode');
    
        /**
         * Generating Site Menu using MelisFrontMenuPlugin Plugin
         */
        $menuPlugin = $this->MelisFrontMenuPlugin();
        $menuParameters = array(
            'template_path' => 'MelisDemoCommerce/plugin/menu',
            'pageIdRootMenu' => $siteConfig['conf']['home_page'],
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
        );
        $this->layout()->addChild($cartPlugin->render($cartParameters), 'menuCart');
        
        // add generated view to children views for displaying it in the contact view
        $breadcrumb = $breadcrumbPlugin->render($breadcrumbParameters);
        $this->layout()->addChild($breadcrumb, 'pageBreadcrumb');
    
        return parent::onDispatch($event);
    }
}