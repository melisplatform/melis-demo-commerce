<?php 

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisDemoCommerce\Listener;

use MelisFront\Service\MelisSiteConfigService;
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
        	    // Getting the Datas from the Event Parameters
        	    $params = $e->getParams();

        	    $viewVariables = $params['view']->getVariables();
        	    
        	    if ($params['view']->getTemplate() == 'MelisDemoCommerce/plugin/menu' && !empty($viewVariables['menu']))
        	    {
                    /** @var MelisSiteConfigService $siteConfigSrv */
                    $siteConfigSrv = $this->serviceLocator->get('MelisSiteConfigService');

        	        $container = new Container('melisplugins');
        	        $langId = $container['melis-plugins-lang-id'];

        	        $sumMenuLimit = $siteConfigSrv->getSiteConfigByKey('sub_menu_limit', $params['pluginFronConfig']['pageId']);
        	        $newsMenuPageId = $siteConfigSrv->getSiteConfigByKey('news_menu_page_id', $params['pluginFronConfig']['pageId']);
        	        $cataloguePages = $siteConfigSrv->getSiteConfigByKey('catalogue_pages', $params['pluginFronConfig']['pageId']);

        	        // Geeting the custom datas from site config
        	        $limit = (!empty($sumMenuLimit)) ? $sumMenuLimit : null;
                    $newsMenuPageId = (!empty($newsMenuPageId)) ? $newsMenuPageId : null;
                    $cataloguePages = (!empty($cataloguePages)) ? $cataloguePages : array();
                    
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