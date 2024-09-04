<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2015 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisDemoCommerce\Controller;


use MelisFront\Service\MelisSiteConfigService;

class NewsController extends BaseController
{
    /**
     * This method will render the list of news
     *
     * @return \Laminas\View\Model\ViewModel
     */
    public function listAction()
    {
        /** @var MelisSiteConfigService $siteConfigSrv */
        $siteConfigSrv = $this->getServiceManager()->get('MelisSiteConfigService');
        /**
         * Listing News using MelisCmsNewsListNewsPlugin
         */
        $listNewsPluginView = $this->MelisCmsNewsListNewsPlugin();
        $listNewsParameters = array(
            'template_path' => 'MelisDemoCommerce/plugin/news-list',
            'pageId' => $this->idPage,
            'pageIdNews' => $siteConfigSrv->getSiteConfigByKey('news_details_page_id', $this->idPage),
            'pagination' => array(
                'nbPerPage' => 6
            ),
            'filter' => array(
                'column' => 'cnews_publish_date',
                'order' => 'DESC',
                'unpublish_filter' => true,
                'site_id' => $siteConfigSrv->getSiteConfigByKey('site_id', $this->idPage),
            )
        );
        
        // add generated view to children views for displaying it in the contact view
        $this->view->addChild($listNewsPluginView->render($listNewsParameters), 'listNews');
        
        $this->view->setVariable('renderMode', $this->renderMode);
        $this->view->setVariable('idPage', $this->idPage);
        return $this->view;
    }
    
    /**
     * This methos will render the Details of a single News
     * 
     * @return \Laminas\View\Model\ViewModel
     */
    public function detailsAction()
    {
        /** @var MelisSiteConfigService $siteConfigSrv */
        $siteConfigSrv = $this->getServiceManager()->get('MelisSiteConfigService');
        
        $dateMax = date("Y-m-d H:i:s", strtotime("now"));
        $listNewsPluginView = $this->MelisCmsNewsShowNewsPlugin();
        $listNewsParameters = array(
            'id' => 'newsDetails',
            'template_path' => 'MelisDemoCommerce/plugin/news-details',
        );
        // add generated view to children views for displaying it in the contact view
        $this->view->addChild($listNewsPluginView->render($listNewsParameters), 'newsDetails');
        
        /**
         * Generating Homepage Latest News slider using MelisCmsNewsLatestNewsPlugin Plugin
         */
        $latestNewsPluginView = $this->MelisCmsNewsLatestNewsPlugin();
        $latestNewsParameters = array(
            'template_path' => 'MelisDemoCommerce/plugin/latest-news',
            'filter' => array(
                'column' => 'cnews_publish_date',
                'order' => 'DESC',
                'limit' => 10,
                'unpublish_filter' => true,
                'date_max' => null,
                'site_id' => $siteConfigSrv->getSiteConfigByKey('site_id', $this->idPage),
            )
        );
        // add generated view to children views for displaying it in the contact view
        $this->view->addChild($latestNewsPluginView->render($latestNewsParameters), 'latestNews');
        
        $this->view->setVariable('renderMode', $this->renderMode);
        $this->view->setVariable('idPage', $this->idPage);
        return $this->view;
    }
}