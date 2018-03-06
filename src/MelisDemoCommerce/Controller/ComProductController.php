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
use Zend\Stdlib\ArrayUtils;

class ComProductController extends BaseController
{
    public function indexAction()
    {
        
        // Getting the Site config "MelisDemoCommerce.config.php"
        $siteConfig = $this->getServiceLocator()->get('config');
        $siteConfig = $siteConfig['site']['MelisDemoCommerce'];
        $siteDatas = $siteConfig['datas'];
        $routeParams['m_p_id'] = $this->params()->fromRoute('productId');
        // if rendering is back office, assign first product
        if($this->renderMode == 'melis'){
            $routeParams['m_p_id'] = (!empty($siteDatas['defaultProduct']) ? $siteDatas['defaultProduct'] : '');
        }
        /**
         * Generating show product using MelisCommerceProductShowPlugin
         */
        $productView = $this->MelisCommerceProductShowPlugin();
        // pass custom template paths
        $params = array(
            'template_path' => 'MelisDemoCommerce/plugin/show-product',
            'template_path_attributes_view' => 'MelisDemoCommerce/plugin/show-attributes',
            'template_path_add_to_cart_view' => 'MelisDemoCommerce/plugin/show-add-to-cart',
        );
        $params = ArrayUtils::merge($params, $routeParams);
        
        // add generated view to children views for displaying it in the home page view
        $this->view->addChild($productView->render($params), 'showProductView');
        
        /**
         * Generating related products using MelisCommerceFilterMenuCategoryListPlugin
         */
        $relatedProductView = $this->MelisCommerceProductsRelatedPlugin();
        $params = array(
            'template_path' => 'MelisDemoCommerce/plugin/related-products',            
        );
        $params = ArrayUtils::merge($params, $routeParams);
        // add generated view to children views for displaying it in the home page view
        $this->view->addChild($relatedProductView->render($params), 'relatedProductsView');
        
        /**
         * Retrieving Categeries related to the selected product
         * using the Product Service
         */
        $productSrv = $this->getServiceLocator()->get('MelisComProductService');
        $prdCat = null;
        
        if(!empty($routeParams['m_p_id'])){
            $prdCat = $productSrv->getProductCategories($routeParams['m_p_id']);
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
        
        $this->layout()->setVariables(array(
            'pageJs' => array(
                '/MelisDemoCommerce/js/product-attributes.js',
                '/MelisDemoCommerce/js/cart.js',
            ),
        ));
        
        $this->view->setVariable('idPage', $this->idPage);
        $this->view->setVariable('pageBanner', $pageBanner);
        return $this->view;
    }
    
    public function getVariantCommonAttributesAction()
    {
        $result = array();
        $request = $this->getRequest();
        
        if ($request->isPost())
        {
            $attributeShowPlugin = $this->MelisCommerceAttributesShowPlugin();
            // add generated view to children views for displaying it in the contact view
            $result = $attributeShowPlugin->render(array('m_is_submit' => true))->getVariables();
            
            $result = array(
                'variant' => ($result->variant) ? $result->variant->getVariant() : array(),
                'variant_price' => $result->variant_price,
                'variant_attr' => $result->variant_attr,
                'variant_stock' => $result->variant_stock,
            );
        }
        
        return new JsonModel($result);
    }
    
    public function addToCartAction()
    {
        // Default Values
        $status  = 0;
        $errors  = array();
         
        $request = $this->getRequest();
    
        if ($request->isPost())
        {
            /**
             * Validating the AddToCart form by Calling the
             * plugin and adding parameter as flag "m_is_submit" to submit the form
             */
            $addtoCart = $this->MelisCommerceCartAddPlugin();
            // add generated view to children views for displaying it in the contact view
            $result = $addtoCart->render(array('m_is_submit' => true))->getVariables();
    
            // Retrieving view variable from view
            $errors = $result->errors;
            if (empty($errors))
            {
                $status = 1;
            }
            
            $cartMenuPlugin = $this->MelisCommerceCartMenuPlugin();
            $items = $cartMenuPlugin->render()->getVariables();
           
        }
    
        $response = array(
            'basket' => $items->basket,
            'success' => $status,
            'errors' => $errors,
        );
         
        return new JsonModel($response);
    }
    
    public function removeItemFromCartAction()
    {
        $result = array();
        $variantId = array();
        $request = $this->getRequest();
        
        if ($request->isPost())
        {
            $requestVar = get_object_vars($this->getRequest()->getPost());
            
            // Use MelisCommerceCheckoutCartPlugin to delete item from cart
            $checkOutcartPlugin = $this->MelisCommerceCheckoutCartPlugin();
            
            $checkOutCartParameters = array(
                'm_country_id' => !empty($requestVar['m_country_id'])? $requestVar['m_country_id'] : null,
                'm_v_id_remove' => $requestVar['m_v_id_remove'],
            );
            
            $result = $checkOutcartPlugin->render($checkOutCartParameters)->getVariables();
            foreach($result['checkOutCart'] as $item){
                $variantId[] = $item['var_id'];
            }
            
            if(!in_array($requestVar['m_v_id_remove'], $variantId)){
                $result['deleteSuccess'] = 1;
            }else{
                $result['deleteSuccess'] = 0;
            }
        }
        
        return new JsonModel($result);
    }
}