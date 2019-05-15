<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2015 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisDemoCommerce\Controller;

use MelisDemoCommerce\Controller\BaseController;
use Zend\View\Model\JsonModel;
use Zend\Stdlib\Parameters;
use Zend\Stdlib\ArrayUtils;

class ComProductController extends BaseController
{
    public function indexAction()
    {
        // Getting the Site config "MelisDemoCommerce.config.php"
        $siteConfig = $this->getServiceLocator()->get('config');
        $siteConfig = $siteConfig['site']['MelisDemoCommerce'];
        $siteDatas = $siteConfig['datas'];
        
        $productId = array(
            'm_product_id' => $this->params()->fromRoute('productId', null)
        );

        if(empty($productId['m_product_id'])){
            if(isset($this->getServiceLocator()->get('request')->getQuery()->toArray()["m_p_id"])){
                $productId['m_product_id'] = $this->getServiceLocator()->get('request')->getQuery()->toArray()["m_p_id"];
            }
        }

        // Merging current data from url 
        $params = ArrayUtils::merge($this->getServiceLocator()->get('request')->getQuery()->toArray(), $productId);
        
        // Setting the Get data to make product id of the plugin dynamic
        $postParam = new Parameters($params);
        $this->getServiceLocator()->get('request')->setQuery($postParam);
        
        /**
         * Generating show product using MelisCommerceProductShowPlugin
         */
        $productPlugin = $this->MelisCommerceProductShowPlugin();
        // pass custom template paths
        $pluginParams = array(
            'template_path' => 'MelisDemoCommerce/plugin/show-product',
            'm_product_country' => $siteDatas['site_country_id'],
        );
        $productPlugin = $productPlugin->render($pluginParams);
        // add generated view to children views for displaying it in the home page view
        $this->view->addChild($productPlugin, 'showProductView');
        
        /**
         * Generating related products using MelisCommerceRelatedProductsPlugin
         */
        $relatedProductView = $this->MelisCommerceRelatedProductsPlugin();
        $params = array(
            'template_path' => 'MelisDemoCommerce/plugin/related-products',  
            'm_product_id' => $this->params()->fromRoute('productId', null)
        );
        // add generated view to children views for displaying it in the home page view
        $this->view->addChild($relatedProductView->render($params), 'relatedProductsView');
        
        // Page banner
        $this->setPageBanner();
        
        $this->view->setVariable('idPage', $this->idPage);
        
        return $this->view;
    }
    
    /**
     * Getting the category banner image for page
     */
    private function setPageBanner()
    {
        // Getting the Site config "MelisDemoCommerce.config.php"
        $siteConfig = $this->getServiceLocator()->get('config');
        $siteConfig = $siteConfig['site']['MelisDemoCommerce'];
        $siteDatas = $siteConfig['datas'];
        /**
         * Retrieving Categeries related to the selected product
         * using the Product Service
         */
        $productSrv = $this->getServiceLocator()->get('MelisComProductService');
        $prdCat = null;
        
        $prdId = $this->params()->fromRoute('productId', null);
        
        if(!empty($prdId))
        {
            $prdCat = $productSrv->getProductCategories($prdId);
        }
        
        $pageBanner = '';
        if (!empty($prdCat))
        {
            $catSrv = $this->getServiceLocator()->get('MelisComCategoryService');
            
            $siteConfigCatalogue = $siteDatas['catalogue_pages'];
            /**
             * This process will try to get the Category the will match
             * on site config "catalogue_pages"
             * if the Category found on the list of parent categories
             * this will be the category to use for generating breadcrumb
             *
             * if the process did not found, this will get the first category as
             * bases in generating breadcrumb
             *
             * $category = $prdCat[0];
             */
            $category = array();
            foreach ($prdCat As $key => $val)
            {
                $temp = array();
                $temp = $catSrv->getParentCategory($val->cat_father_cat_id, $temp);
                
                foreach ($temp As $tKey => $tval)
                {
                    foreach ($siteConfigCatalogue As $cKey => $cVal)
                    {
                        if ($cVal['category_id'] == $tval['cat_id'])
                        {
                            $category = $temp[count($temp) - 1];
                            break;
                        }
                    }
                    
                    if (!empty($category))
                    {
                        break;
                    }
                }
                
                if (!empty($category))
                {
                    break;
                }
            }
            
            if (empty($category))
            {
                $category = $prdCat[0];
                $category = $category->getArrayCopy();
            }
            
            /**
             * Using MelisComDocumentService service Category will get the docoment
             * with the type of "IMG" and subType with "Default" to get the
             * image for Default
             * with the return is multi array
             */
            $documentSrv = $this->getServiceLocator()->get('MelisComDocumentService');
            $doc = $documentSrv->getDocumentsByRelationAndTypes('category', $category['cat_id'], 'IMG', array('DEFAULT'));
            if (!empty($doc))
            {
                $pageBanner = $doc[0]->doc_path;
            }
        }
        
        $this->view->setVariable('pageBanner', $pageBanner);
    }
    
    public function getVariantCommonAttributesAction()
    {
        $result = array();
        $request = $this->getRequest();
        
        if ($request->isPost())
        {
            $post = $request->getPost();
            
            $productId = $post['productId'];
            $attrSelection = $post['attrSelection'];
            $action = $post['action'];
            
            $demoCommreceSrv = $this->getServiceLocator()->get('DemoCommerceService');
            $result = $demoCommreceSrv->getVariantbyAttributes($productId, $attrSelection, $action);
        }
        
        return new JsonModel($result);
    }
    
    public function addToCartAction()
    {
        // Default Values
        $status  = 0;
        $errors  = array();
        $cartList = '';
        
        $request = $this->getRequest();
        
        if ($request->isPost())
        {
            /**
             * Validating the AddToCart form by Calling the plugin
             */
            $addtoCart = $this->MelisCommerceAddToCartPlugin();
            // add generated view to children views for displaying it in the contact view
            $result = $addtoCart->render()->getVariables();
            
            // Retrieving view variable from view
            $errors = $result->errors;
            if (empty($errors))
            {
                $status = 1;
                
                // Getting the Site config "MelisDemoCommerce.config.php"
                $siteConfig = $this->getServiceLocator()->get('config');
                $siteConfig = $siteConfig['site']['MelisDemoCommerce'];
                $siteDatas = $siteConfig['datas'];
                
                $cartPlugin = $this->MelisCommerceCartPlugin();
                $menuParameters = array(
                    'template_path' => 'MelisDemoCommerce/plugin/menu-cart',
                    'id' => 'menuCartPlugin',
                );
                $cartViewModel = $cartPlugin->render($menuParameters);
                
                // Rendering the viewmodel of the plugin 
                $viewRender = $this->getServiceLocator()->get('ViewRenderer');
                $cartList = $viewRender->render($cartViewModel);
            }
        }
        
        $response = array(
            'cartList' => $cartList,
            'success' => $status,
            'errors' => $errors,
        );
         
        return new JsonModel($response);
    }
    
    /**
     * Cart item deletion using plugin MelisCommerceCartPlugin()
     * 
     * @return \Zend\View\Model\JsonModel
     */
    public function removeItemFromCartAction()
    {
        $result = array();
        $request = $this->getRequest();
        
        if ($request->isPost())
        {
            /**
             * To remove spicific variant from cart/basket
             * this plugin accepting data "cart_variant_remove" with the value
             * of the variant to be remove 
             */
            $cartPlugin = $this->MelisCommerceCartPlugin();
            $cartViewModel = $cartPlugin->render();
            $cartVars = $cartViewModel->getVariables();
            
            $total = $cartVars['total'];
            $currency = $cartVars['currency'];
            
            $result = array(
                'success' => 1,
                'totalAmount' => $currency.number_format($total, 2),
                'totalItemCount' => $cartVars->cartList->getPages()->totalItemCount,
            );
        }
        
        return new JsonModel($result);
    }
}