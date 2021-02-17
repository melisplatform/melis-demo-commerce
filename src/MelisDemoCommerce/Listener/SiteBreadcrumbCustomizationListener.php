<?php 

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisDemoCommerce\Listener;

use MelisFront\Service\MelisSiteConfigService;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Session\Container;

class SiteBreadcrumbCustomizationListener extends SiteGeneralListener
{
	public function attach(EventManagerInterface $events, $priority = 1)
	{
		$this->attachEventListener(
			$events,
			'*',
			[
				'MelisFrontBreadcrumbPlugin_melistemplating_plugin_end',
			],
			function($e){
				// Getting the Service Manager from param target
				$serviceManager = $e->getTarget()->getServiceManager();

				/** @var MelisSiteConfigService $siteConfigSrv */
				$siteConfigSrv = $serviceManager->get('MelisSiteConfigService');

				// Getting the Datas from the Event Parameters
				$params = $e->getParams();
				
				$viewVariables = $params['view']->getVariables();
				
				if (!empty($viewVariables['breadcrumb']))
				{
					
					$router= $serviceManager->get('router');
					$request = $serviceManager->get('request');
					$routeMatch = $router->match($request);
					
					$route = $routeMatch->getParams();
				
					try
					{
						$productId = !empty($route['productId']) ? $route['productId'] : null;
						
						$lastPageId = $viewVariables['breadcrumb'][count($viewVariables['breadcrumb']) -1]['idPage'];
						
						$melisDemoCommerceSrv = $serviceManager->get('DemoCommerceService');

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
						
					} catch (\Exception $e){}
				}
			},
			100
		);
	}
}