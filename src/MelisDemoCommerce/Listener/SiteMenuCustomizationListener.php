<?php 

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisDemoCommerce\Listener;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Session\Container;

class SiteMenuCustomizationListener implements ListenerAggregateInterface
{
    private $serviceLocator;
	
    public function attach(EventManagerInterface $events)
    {
        $sharedEvents      = $events->getSharedManager();
        
        $callBackHandler = $sharedEvents->attach(
            '*',
            array(
                'MelisFrontMenuPlugin_melistemplating_plugin_end',
            ),
        	function($e){
        	    // Getting the Service Locator from param target
        	    $this->serviceLocator = $e->getTarget()->getServiceLocator();
        	    // Getting the Site config "MelisDemoCommerce.config.php"
        	    $siteConfig = $this->serviceLocator->get('config');
        	    $siteConfig = $siteConfig['site']['MelisDemoCommerce'];
        	    $siteDatas = $siteConfig['datas'];
        	    // Getting the Datas from the Event Parameters
        	    $params = $e->getParams();
        	    
        	    $viewVariables = $params['view']->getVariables();
        	    
        	    if ($params['view']->getTemplate() == 'MelisDemoCommerce/plugin/menu' && !empty($viewVariables['menu']))
        	    {
        	        $container = new Container('melisplugins');
        	        $langId = $container['melis-plugins-lang-id'];
        	        
        	        // Geeting the custom datas from site config
        	        $limit = (!empty($siteDatas['sub_menu_limit'])) ? $siteDatas['sub_menu_limit'] : null;
                    $newsMenuPageId = (!empty($siteDatas['news_menu_page_id'])) ? $siteDatas['news_menu_page_id'] : null;
                    $cataloguePages = (!empty($siteDatas['catalogue_pages'])) ? $siteDatas['catalogue_pages'] : array();
                    
                    // Getting the Subpages of Home Page
                    $sitePages = (!empty($viewVariables['menu'][0]['pages'])) ? $viewVariables['menu'][0]['pages'] : array();
                    
                    // Customize Site menu using MelisDemoCmsService
                    $melisDemoCommerceSrv = $this->serviceLocator->get('DemoCommerceService');
                    $params['view']->menu = $melisDemoCommerceSrv->customizeSiteMenu($sitePages, 1, $limit, $newsMenuPageId, $cataloguePages, $langId);
        	    }
        	},
        100);
        
        $this->listeners[] = $callBackHandler;
    }
    
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }
}