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

class SiteMenuCustomizationListener extends SiteGeneralListener
{
    private $serviceManager;
    
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->attachEventListener(
            $events,
            '*',
            [
                'MelisFrontMenuPlugin_melistemplating_plugin_end',
            ],
            function($e){
                // Getting the Service Manager from param target
                $this->serviceManager = $e->getTarget()->getServiceManager();
                // Getting the Datas from the Event Parameters
                $params = $e->getParams();

                $viewVariables = $params['view']->getVariables();
                
                if ($params['view']->getTemplate() == 'MelisDemoCommerce/plugin/menu' && !empty($viewVariables['menu']))
                {
                    /** @var MelisSiteConfigService $siteConfigSrv */
                    $siteConfigSrv = $this->serviceManager->get('MelisSiteConfigService');

                    $container = new Container('melisplugins');
                    $langId = $container['melis-plugins-lang-id'];

                    $sumMenuLimit = $siteConfigSrv->getSiteConfigByKey('sub_menu_limit', $params['pluginFrontConfig']['pageId']);
                    $newsMenuPageId = $siteConfigSrv->getSiteConfigByKey('news_menu_page_id', $params['pluginFrontConfig']['pageId']);
                    $cataloguePages = $siteConfigSrv->getSiteConfigByKey('catalogue_pages', $params['pluginFrontConfig']['pageId']);

                    // Geeting the custom datas from site config
                    $limit = (!empty($sumMenuLimit)) ? $sumMenuLimit : null;
                    $newsMenuPageId = (!empty($newsMenuPageId)) ? $newsMenuPageId : null;
                    $cataloguePages = (!empty($cataloguePages)) ? $cataloguePages : array();
                    
                    // Getting the Subpages of Home Page
                    $sitePages = (!empty($viewVariables['menu'][0]['pages'])) ? $viewVariables['menu'][0]['pages'] : array();
                    
                    // Customize Site menu using MelisDemoCmsService
                    $melisDemoCommerceSrv = $this->serviceManager->get('DemoCommerceService');
                    $params['view']->menu = $melisDemoCommerceSrv->customizeSiteMenu($sitePages, 1, $limit, $newsMenuPageId, $cataloguePages, $langId);
                }
            },
            100
        );
    }
}