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
         * Generating Men product list slider
         */
        $melisCommerceCategoryProductListPlugin = $this->MelisCommerceCategoryProductListPlugin();
        $categorySliderListParameters = array(
            'id' => 'homepageCategoryProductSlider1',
            'template_path' => 'MelisDemoCommerce/plugin/category-product-list-slider',
            'm_category_option' => array(
                'm_category_ids' => $siteDatas['homepage_category_product_slider_1'],
                'm_include_sub_category_products' => true,
            ),
            'm_product_option' => array(
                'm_country_id' => $siteDatas['price_country_id'],
                'm_prd_limit' => $siteDatas['homepage_category_product_slider_limit'],
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
                'm_category_ids' => $siteDatas['homepage_category_product_slider_2'],
                'm_include_sub_category_products' => true,
                
            ),
            'm_product_option' => array(
                'm_country_id' => $siteDatas['price_country_id'],
                'm_prd_limit' => $siteDatas['homepage_category_product_slider_limit'],
            ),
        );
        
        // add generated view to children views for displaying it in the home page view
        $this->view->addChild($melisCommerceCategoryProductListPlugin->render($categorySliderListProductsParameters), 'homepageCategoryProductSlider2');
        
        $this->layout()->setVariables(array(
            'pageJs' => array(
                '/MelisDemoCommerce/js/MelisPlugins/MelisDemoCommerce.MelisCommerceCategoryProductListPlugin.init.js',
                //'/MelisDemoCommerce/js/MelisPlugins/MelisDemoCommerce.MelisCommerceCategoryTree.init.js'
            ),
        ));
        
        $this->view->setVariable('idPage', $this->idPage);
        $this->view->setVariable('renderType', $this->renderType);
        $this->view->setVariable('renderMode', $this->renderMode);
        return $this->view;
    }
}