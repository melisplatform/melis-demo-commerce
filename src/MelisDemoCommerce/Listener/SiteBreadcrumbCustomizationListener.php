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

class SiteBreadcrumbCustomizationListener implements ListenerAggregateInterface
{
    private $serviceLocator;
	
    public function attach(EventManagerInterface $events)
    {
        $sharedEvents      = $events->getSharedManager();
        
        $callBackHandler = $sharedEvents->attach(
        	'*',
            array(
                'MelisFrontBreadcrumbPlugin_melistemplating_plugin_end',
            ),
        	function($e){
        	    // Getting the Service Locator from param target
        	    $this->serviceLocator = $e->getTarget()->getServiceLocator();

                /** @var MelisSiteConfigService $siteConfigSrv */
                $siteConfigSrv = $this->serviceLocator->get('MelisSiteConfigService');

        	    // Getting the Datas from the Event Parameters
        	    $params = $e->getParams();
        	    
        	    $viewVariables = $params['view']->getVariables();
        	    
        	    if (!empty($viewVariables['breadcrumb']))
        	    {
        	        
        	        $router= $this->serviceLocator->get('router');
        	        $request = $this->serviceLocator->get('request');
        	        $routeMatch = $router->match($request);
        	        
        	        $route = $routeMatch->getParams();
        	       
        	        try
        	        {
        	            $productId = !empty($route['productId']) ? $route['productId'] : null;
        	            
        	            $lastPageId = $viewVariables['breadcrumb'][count($viewVariables['breadcrumb']) -1]['idPage'];
        	            
        	            $melisDemoCommerceSrv = $this->serviceLocator->get('DemoCommerceService');

        	            $siteConfigCatalogue = $siteConfigSrv->getSiteConfigByKey('catalogue_pages', $params['pluginFronConfig']['pageId']);

        	            $container = new Container('melisplugins');
        	            $langId = $container['melis-plugins-lang-id'];

        	            $productPageid = $siteConfigSrv->getSiteConfigByKey('product_page_id', $params['pluginFronConfig']['pageId']);

            	        if ($lastPageId == $productPageid)
            	        {
            	            // Customize Site menu using MelisDemoCmsService
            	            $viewVariables['breadcrumb'] = $melisDemoCommerceSrv->customizeSiteBreadcrumb($viewVariables['breadcrumb'], $productPageid, $langId, $siteConfigCatalogue, $productId);
            	        }
            	        
            	        $categoryId = !empty($route['categoryId']) ? $route['categoryId'] : null;
            	        
            	        if(!is_null($categoryId)){
            	            $viewVariables['breadcrumb'] = $melisDemoCommerceSrv->customizeSiteBreadcrumbCategory($viewVariables['breadcrumb'], $lastPageId, $langId, $siteConfigCatalogue, $categoryId);
            	        }
            	        
        	        }
        	        catch (\Exception $e){}
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