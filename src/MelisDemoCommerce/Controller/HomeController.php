<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2015 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisDemoCommerce\Controller;

use MelisFront\Service\MelisSiteConfigService;

class HomeController extends BaseController
{
    public function indexAction()
    {
        /** @var MelisSiteConfigService $siteConfigSrv */
        $siteConfigSrv = $this->getServiceManager()->get('MelisSiteConfigService');
        /**
         * Generating Homepage header Slider using MelisCmsSliderShowSliderPlugin Plugin
         */
        $showSlider = $this->MelisCmsSliderShowSliderPlugin();
        $showSliderParameters = array(
            'template_path' => 'MelisDemoCommerce/plugin/homepage-slider',
            'id' => 'showSliderHomepage',
            'pageId' => $this->idPage,
            'sliderId' => $siteConfigSrv->getSiteConfigByKey('homepage_header_slider', $this->idPage),
        );
        // add generated view to children views for displaying it in the home page view
        $this->view->addChild($showSlider->render($showSliderParameters), 'homePageSlider');

        
        $showListForFolderPlugin = $this->MelisFrontShowListFromFolderPlugin();
        $menuParameters = array(
            'template_path' => 'MelisDemoCommerce/plugin/testimonial-slider',
            'pageId' => $this->idPage,
            'pageIdFolder' => $siteConfigSrv->getSiteConfigByKey('testimonial_id', $this->idPage),
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
            'pageIdNews' => $siteConfigSrv->getSiteConfigByKey('news_details_page_id', $this->idPage),
            'filter' => array(
                'column' => 'cnews_publish_date',
                'order' => 'DESC',
                'date_min' => null,
                'date_max' => null,
                'unpublish_filter' => true,
                'site_id' => $siteConfigSrv->getSiteConfigByKey('site_id', $this->idPage),
                'search' => '',
                'limit' => 6,
            )
        );
        // add generated view to children views for displaying it in the home page view
        $this->view->addChild($latestNewsPluginView->render($latestNewsParameters), 'latestNews');
        
        /**
         * Generating Men product list slider
         */
        $melisCommerceCategoryProductListPlugin = $this->MelisCommerceCategoryProductListPlugin();
        $categorySliderListParameters = array(
            'id' => 'homepageCategoryProductSlider1',
            'template_path' => 'MelisDemoCommerce/plugin/category-product-list-slider',
            'm_category_option' => array(
                'm_category_ids' => $siteConfigSrv->getSiteConfigByKey('homepage_category_product_slider_1', $this->idPage),
                'm_include_sub_category_products' => true,
            ),
            'm_product_option' => array(
                'm_country_id' => $siteConfigSrv->getSiteConfigByKey('price_country_id', $this->idPage),
                'm_prd_limit' => $siteConfigSrv->getSiteConfigByKey('homepage_category_product_slider_limit', $this->idPage),
            ),
        );
        // add generated view to children views for displaying it in the home page view
        $this->view->addChild($melisCommerceCategoryProductListPlugin->render($categorySliderListParameters), 'homepageCategoryProductSlider1');
        
        /**
         * Generating New Arrivals, Best Seller, Special Offers slider
         */
        $melisCommerceCategoryProductListPlugin = $this->MelisCommerceCategoryProductListPlugin();
        $categorySliderListProductsParameters = array(
            'id' => 'homepageCategoryProductSlider2',
            'template_path' => 'MelisDemoCommerce/plugin/category-product-list-slider',
            'm_category_option' => array(
                'm_category_ids' => $siteConfigSrv->getSiteConfigByKey('homepage_category_product_slider_2', $this->idPage),
                'm_include_sub_category_products' => true,
                
            ),
            'm_product_option' => array(
                'm_country_id' => $siteConfigSrv->getSiteConfigByKey('price_country_id', $this->idPage),
                'm_prd_limit' => $siteConfigSrv->getSiteConfigByKey('homepage_category_product_slider_limit', $this->idPage),
            ),
        );
        
        // add generated view to children views for displaying it in the home page view
        $this->view->addChild($melisCommerceCategoryProductListPlugin->render($categorySliderListProductsParameters), 'homepageCategoryProductSlider2');

        $this->view->setVariable('idPage', $this->idPage);
        $this->view->setVariable('renderType', $this->renderType);
        $this->view->setVariable('renderMode', $this->renderMode);
        return $this->view;
    }
}