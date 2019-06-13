<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2015 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisDemoCommerce\Controller;

use MelisDemoCommerce\Controller\BaseController;
use MelisFront\Service\MelisSiteConfigService;

class ComCatalogueController extends BaseController
{
    public function indexAction()
    {
        /** @var MelisSiteConfigService $siteConfigSrv */
        $siteConfigSrv = $this->getServiceLocator()->get('MelisSiteConfigService');

        $catalogueFilter = array();
        $queryParameters = $this->params()->fromQuery();
        $routeparameters = array();
        
        // if no filters from query, check category ids parameters from route
        if(empty($queryParameters)){
            $routeparameters['m_box_category_tree_ids_selected'][] = $this->params()->fromRoute('categoryId');
            $queryParameters = $routeparameters;
        }
        
        $pageBanner = '';
        foreach ($siteConfigSrv->getSiteConfigByKey('catalogue_pages', $this->idPage) As $val)
        {
            if ($this->idPage == $val['page_id'])
            {
                /**
                 * Using MelisComDocumentService service Category will get the docoment
                 * with the type of "IMG" and subType with "Default" to get the
                 * image for Default
                 * with the return is multi array
                 */
                $documentSrv = $this->getServiceLocator()->get('MelisComDocumentService');
                $doc = $documentSrv->getDocumentsByRelationAndTypes('category', $val['category_id'], 'IMG', array('DEFAULT'));
                if (!empty($doc))
                {
                    $pageBanner = $doc[0]->doc_path;
                }
            }
        }

        /**
         * Generating search filter using MelisCommerceProductSearchPlugin
         */
        $searchFilter = $this->MelisCommerceProductSearchPlugin();
        $params = array(
            'template_path' => 'MelisDemoCommerce/plugin/product-search',
        );
        $params = array_merge($params, $queryParameters);
        // add generated view to children views for displaying it in the home page view
        $this->view->addChild($searchFilter->render($params), 'searchBox');
        
        /**
         * Generating Categories list filter using MelisCommerceCategoryTreePlugin
         */
        $categoryList = $this->MelisCommerceCategoryTreePlugin();
        $params = array(
            'template_path' => 'MelisDemoCommerce/plugin/category-tree',
            'parent_category_id' => $siteConfigSrv->getSiteConfigByKey('parent_category_id', $this->idPage),
        );
        $params = array_merge($params, $queryParameters);
        // add generated view to children views for displaying it in the home page view
        $this->view->addChild($categoryList->render($params), 'categoryListFilter');
        
        /**
         * Generating price filter using MelisCommerceProductPriceRangePlugin
         */
        $priceFilter = $this->MelisCommerceProductPriceRangePlugin();
        $priceFilterParameters = array(
            'template_path' => 'MelisDemoCommerce/plugin/product-price-range',
        );
        
        if (!empty($queryParameters['m_box_category_tree_ids_selected'])){
            $priceFilterParameters = array_merge($priceFilterParameters, $queryParameters['m_box_category_tree_ids_selected']);
        }
        
        // add generated view to children views for displaying it in the home page view
        $this->view->addChild($priceFilter->render($priceFilterParameters), 'priceFilter');
        
        /**
         * Generating color attribute filter using MelisCommerceProductAttributePlugin
         */
        $colorAttributeFilter = $this->MelisCommerceProductAttributePlugin();
        $colorAttributeFilterParameters = array(
            'template_path' => 'MelisDemoCommerce/plugin/product-color-attribute',
            'attribute_id' => $siteConfigSrv->getSiteConfigByKey('color_attribute_id', $this->idPage),
        );
        // add generated view to children views for displaying it in the home page view
        $this->view->addChild($colorAttributeFilter->render($colorAttributeFilterParameters), 'colorAttributeFilter');
        
        /**
         * Generating sizes  filter using MelisCommerceProductAttributePlugin
         */
        $sizeAttributeFilter = $this->MelisCommerceProductAttributePlugin();
        $sizeAttributeFilterParameters = array(
            'template_path' => 'MelisDemoCommerce/plugin/product-size-attribute',
            'attribute_id' => $siteConfigSrv->getSiteConfigByKey('size_attribute_id', $this->idPage),
        );
        // add generated view to children views for displaying it in the home page view
        $this->view->addChild($sizeAttributeFilter->render($sizeAttributeFilterParameters), 'sizeAttributeFilter');

        /**
         * Generating category product list using MelisCommerceProductListPlugin
         */
        $categoryProductList = $this->MelisCommerceProductListPlugin();
        $sort = $siteConfigSrv->getSiteConfigByKey('sort_default', $this->idPage);
        $m_col_name = $sort['m_col_name'];
        $m_order = $sort['m_order'];

        $params = array(
            'template_path' => 'MelisDemoCommerce/plugin/product-list',
            'm_col_name' => $m_col_name,
            'm_order' => $m_order,
            'm_box_filter_docs' => array('mainImage' => 'DEFAULT', 'altImage' => 'SMALL'),
            'pagination' => array(
                'm_page_nb_per_page' => 9,
            ),
        );
        $params = array_merge($params, $routeparameters);
        
        // add generated view to children views for displaying it in the home page view
        $this->view->addChild($categoryProductList->render($params), 'categoryProductList');
        $this->layout()->setVariable('queryParameters', $queryParameters);
        $this->view->setVariable('idPage', $this->idPage);
        $this->view->setVariable('pageBanner', $pageBanner);
        $this->view->setVariable('langId', $this->pageLangId);
        return $this->view;
    }
}