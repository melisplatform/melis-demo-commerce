<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2015 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisDemoCommerce\Controller;

use MelisDemoCommerce\Controller\BaseController;

class ComCatalogueController extends BaseController
{
    public function indexAction()
    {       
        // Getting the Site config "MelisDemoCommerce.config.php"
        $siteConfig = $this->getServiceLocator()->get('config');
        $siteConfig = $siteConfig['site']['MelisDemoCommerce'];
        $siteDatas = $siteConfig['datas'];
        $catalogueFilter = array();
        $queryParameters = $this->params()->fromQuery();
        $routeparameters = array();
        
        // if no filters from query, check category ids parameters from route
        if(empty($queryParameters)){
            $routeparameters['m_box_filter_categories_ids_selected'][] = $this->params()->fromRoute('categoryId');
            $queryParameters = $routeparameters;
        }
        
        $pageBanner = '';
        foreach ($siteDatas['catalogue_pages'] As $val)
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
         * Generating Categories list filter using MelisCommerceFilterMenuCategoryListPlugin
         */
        $categoryList = $this->MelisCommerceFilterMenuCategoryListPlugin();
        $params = array(
            'template_path' => 'MelisDemoCommerce/plugin/category-list-filter',
            'parent_category_id' => $siteDatas['parent_category_id'],
        );
        $params = array_merge($params, $queryParameters);
        // add generated view to children views for displaying it in the home page view
        $this->view->addChild($categoryList->render($params), 'categoryListFilter');
        
        /**
         * Generating price filter using MelisCommerceFilterMenuPriceValueBoxPlugin 
         */
        $priceFilter = $this->MelisCommerceFilterMenuPriceValueBoxPlugin();
        $priceFilterParameters = array(
            'template_path' => 'MelisDemoCommerce/plugin/category-price-filter',
        );
        
        if (!empty($queryParameters['m_box_filter_categories_ids_selected'])){
            $priceFilterParameters = array_merge($priceFilterParameters, $queryParameters['m_box_filter_categories_ids_selected']);
        }
        
        // add generated view to children views for displaying it in the home page view
        $this->view->addChild($priceFilter->render($priceFilterParameters), 'priceFilter');
        
        /**
         * Generating color attribute filter using MelisCommerceFilterMenuAttributeValueBoxPlugin
         */
        $colorAttributeFilter = $this->MelisCommerceFilterMenuAttributeValueBoxPlugin();
        $colorAttributeFilterParameters = array(
            'template_path' => 'MelisDemoCommerce/plugin/category-color-filter',
            'attribute_id' => $siteDatas['color_attribute_id'],
        );
        // add generated view to children views for displaying it in the home page view
        $this->view->addChild($colorAttributeFilter->render($colorAttributeFilterParameters), 'colorAttributeFilter');
        
        /**
         * Generating sizes  filter using MelisCommerceFilterMenuAttributeValueBoxPlugin
         */
        $sizeAttributeFilter = $this->MelisCommerceFilterMenuAttributeValueBoxPlugin();
        $sizeAttributeFilterParameters = array(
            'template_path' => 'MelisDemoCommerce/plugin/category-size-filter',
            'attribute_id' => $siteDatas['size_attribute_id'],
        );
        // add generated view to children views for displaying it in the home page view
        $this->view->addChild($sizeAttributeFilter->render($sizeAttributeFilterParameters), 'sizeAttributeFilter');
        
        /**
         * Generating category product list using MelisCommerceCategoryListProductsPlugin
         */
        $categoryProductList = $this->MelisCommerceCategoryListProductsPlugin();
        $m_col_name = $siteDatas['sort_default']['m_col_name'];
        $m_order = $siteDatas['sort_default']['m_order'];
        
        $params = array(
            'template_path' => 'MelisDemoCommerce/plugin/category-product-list',
            'm_pag_nb_per_page' => 9,
            'm_col_name' => $m_col_name,
            'm_order' => $m_order,
            'm_box_filter_docs' => array('mainImage' => 'DEFAULT', 'altImage' => 'SMALL'),
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