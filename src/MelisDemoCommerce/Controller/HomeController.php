<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2015 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisDemoCommerce\Controller;

use MelisDemoCommerce\Controller\BaseController;

class HomeController extends BaseController
{
    public function indexAction()
    {
        // Getting the Site config "MelisDemoCommerce.config.php"
        $siteConfig = $this->getServiceLocator()->get('config');
        $siteConfig = $siteConfig['site']['MelisDemoCommerce'];
        $siteDatas = $siteConfig['datas'];
        
        /**
         * Generating Homepage header Slider using MelisCmsSliderShowSliderPlugin Plugin
         */
        $showSlider = $this->MelisCmsSliderShowSliderPlugin();
        $showSliderParameters = array(
            'template_path' => 'MelisDemoCommerce/plugin/homepage-slider',
            'id' => 'showSliderHomepage',
            'pageId' => $this->idPage,
            'sliderId' => $siteDatas['homepage_header_slider'],
        );
        // add generated view to children views for displaying it in the home page view
        $this->view->addChild($showSlider->render($showSliderParameters), 'homePageSlider');
        
        
        $showListForFolderPlugin = $this->MelisFrontShowListFromFolderPlugin();
        $menuParameters = array(
            'template_path' => 'MelisDemoCommerce/plugin/testimonial-slider',
            'pageId' => $this->idPage,
            'pageIdFolder' => $siteDatas['testimonial_id'],
            'renderMode' => $this->renderMode,
        );
        // add generated view to children views for displaying it in the home page view
        $this->view->addChild($showListForFolderPlugin->render($menuParameters), 'testimonialList');
        
        /**
         * Generating Homepage Latest News slider using MelisCmsNewsLatestNewsPlugin Plugin
         */
        $latestNewsPluginView = $this->MelisCmsNewsLatestNewsPlugin();
        $latestNewsParameters = array(
            'template_path' => 'MelisDemoCommerce/plugin/latest-news',
            'pageIdNews' => $siteDatas['news_details_page_id'],
            'filter' => array(
                'column' => 'cnews_publish_date',
                'order' => 'DESC',
                'date_min' => null,
                'date_max' => null,
                'unpublish_filter' => true,
                'site_id' => $siteDatas['site_id'],
                'search' => '',
                'limit' => 6,
            )
        );
        // add generated view to children views for displaying it in the home page view
        $this->view->addChild($latestNewsPluginView->render($latestNewsParameters), 'latestNews');
        
        /**
         * Generating new arrival product list
         */
        $categorySliderListPluginView = $this->MelisCommerceCategorySliderListProductsPlugin();
        $categorySliderListParameters = array(
            'template_path' => 'MelisDemoCommerce/plugin/new-arrivals',
            'm_box_filter_docs' => array('mainImage' => 'DEFAULT', 'altImage' => 'SMALL'),
            'm_box_filter_categories_ids_selected' => $siteDatas['newArrivalCategory'],
        );
        // add generated view to children views for displaying it in the home page view
        $this->view->addChild($categorySliderListPluginView->render($categorySliderListParameters), 'newArrivals');
        
        /**
         * Generating discount, featured, onsale slider
         */
        $categorySliderListProductsPluginView = $this->MelisCommerceCategorySliderListProductsPlugin();
        $categorySliderListProductsParameters = array(
            'template_path' => 'MelisDemoCommerce/plugin/discount-featured-onsale-slider',
            'm_box_filter_docs' => array('mainImage' => 'DEFAULT', 'altImage' => 'SMALL'),
            'm_box_filter_categories_ids_selected' => $siteDatas['discountFeatureOnsale'], // the categories ID,
        );
        
        // add generated view to children views for displaying it in the home page view
        $this->view->addChild($categorySliderListProductsPluginView->render($categorySliderListProductsParameters), 'discountFeaturedOnsaleSlider');
        
        $this->view->setVariable('idPage', $this->idPage);
        $this->view->setVariable('renderType', $this->renderType);
        $this->view->setVariable('renderMode', $this->renderMode);
        return $this->view;
    }
}