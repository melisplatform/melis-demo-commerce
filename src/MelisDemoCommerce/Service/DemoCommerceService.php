<?php

/**
 * Melis Technology (http://www.melistechnology.com)
*
* @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
*
*/

namespace MelisDemoCommerce\Service;

use MelisCore\Service\MelisServiceManager;
use MelisFront\Service\MelisSiteConfigService;
use Laminas\View\Model\JsonModel;
use Laminas\Session\Container;

/**
 * MelisDemoCommerce Services
 */
class DemoCommerceService extends MelisServiceManager
{
    
    private $melisTree;
    private $productSrv;
    private $comLinkSrv;
    private $categorySrv;
    /**
     * This method will customize the Site menu
     * Customization of the menu will also depend on the template to render the Site menu
     * 
     * @param Array $siteMenu, hierarchyral array composed of pages
     * @param In $level, level of hierarchyral, in order to Identify the leveling of subpages
     * @param int $limit, to limit the subpage, set on MelisDemoCommerce.config.php
     * @param int $newsMenuPageId, custom data to be modified, set on MelisDemoCommerce.config.php
     * 
     * @return Array
     */
    public function customizeSiteMenu($siteMenu, $level, $limit = null, $newsMenuPageId = null, $cataloguePages = array(), $langId)
    {
        $this->melisTree = $this->getServiceManager()->get('MelisEngineTree');
        $this->productSrv = $this->getServiceManager()->get('MelisComProductService');
        $this->comLinkSrv = $this->getServiceManager()->get('MelisComLinksService');
        $this->categorySrv = $this->getServiceManager()->get('MelisComCategoryService');
        
        
        // Modified Site Menu handler
        $modifiedSiteMenu = array();
        
        if (!empty($siteMenu))
        {
            foreach ($siteMenu As $key => $val)
            {
                
                // Generating catalogue pages default filters
                foreach($cataloguePages as $page)
                {
                    if($page['page_id'] == $val['idPage'])
                    {
                        $categorySrv = $this->getServiceManager()->get('MelisComCategoryService');
                        $cat = $categorySrv->getCategoryById($page['category_id'])->getCategory();
                        
                        if (!empty($cat))
                        {
                            if ($cat->cat_status)
                            {
                                /**
                                 * Generating list of categories for Menu
                                 */
                                $val['pages'] = $this->getSubCategoriesForMenu($page['category_id'], $val['idPage'], $val['uri'], $langId);
                            
                                // Generating Category link using MelisComLinkService
                                $comLinkSrv = $this->getServiceManager()->get('MelisComLinksService');
                                $val['uri'] = $comLinkSrv->getPageLink('category', $page['category_id'], $langId, false);
                            
                                /**
                                 * Using MelisComDocumentService service Category will get the docoment
                                 * with the type of "IMG" and subType with "Menu" to get the
                                 * image for Menu
                                 * with the return is multi array
                                 */
                                $documentSrv = $this->getServiceManager()->get('MelisComDocumentService');
                                $doc = $documentSrv->getDocumentsByRelationAndTypes('category', $page['category_id'], 'IMG', array('MENU'));
                                if (!empty($doc))
                                {
                                    $val['menu_img'] = $doc[0]->doc_path;
                                }
                            }
                        }
                    }
                }
                
                // Scaping the recursive after customization of the News Menu
                $scapeRecursive = false;
                if ($val['idPage'] == $newsMenuPageId)
                {
                    // Retrieving the News list using another method
                    $val['pages'] = $this->getNewsListFormMenu($val['idPage'], $limit);
                    // scape recursive to avoid infinite loop
                    $scapeRecursive = true;
                }
                
                if (!empty($val['pages']) && !$scapeRecursive)
                {
                    // Calling function itself to retrieve the subpages, using the children as paramater
                    $val['pages'] = $this->customizeSiteMenu($val['pages'], $level + 1, $limit, $newsMenuPageId, array(), $langId);
                }
                
                // Pushing Data to final modified sitemenu
                $modifiedSiteMenu[] = $val;
                
                /**
                 * Static level condition to filter the menu
                 * Layout to render the Menu uses 3 levels
                 */
                if ($level == 3)
                {
                    /**
                     * Checking the if length of the menu on 3rd level 
                     * is equal to the limit set on config then return
                     */
                    if (count($modifiedSiteMenu) == $limit)
                    {
                        return $modifiedSiteMenu;
                    }
                }
            }
        }
        
        return $modifiedSiteMenu;
    }
    
    /**
     * This menu will prepare the list of Category needed for Menu
     * @param int $categoryId
     * @param int $pageId
     * @param string $uri
     * @param int $langId
     * @param int $categoryProductsMenu, list of category id that will have a products sub menu
     * @return Array
     */
    public function getSubCategoriesForMenu($categoryId, $pageId, $uri, $langId)
    {
        /** @var MelisSiteConfigService $siteConfigSrv */
        $siteConfigSrv = $this->getServiceManager()->get('MelisSiteConfigService');
        // Product page id
        $productPageId = $siteConfigSrv->getSiteConfigByKey('product_page_id', $pageId);
        //
        $categoryProductsMenu = $siteConfigSrv->getSiteConfigByKey('category_product_menu', $pageId);

        // Page Tree service to generate Page Link
        $melisTree = $this->getServiceManager()->get('MelisEngineTree');
        
        $productSrv = $this->getServiceManager()->get('MelisComProductService');
        
        $comLinkSrv = $this->getServiceManager()->get('MelisComLinksService');
        
        $pages = array();
        // Sub menu column limit
        $limit = 4;
        /**
         * Service the get the list of specefic category to its sub categories
         * with the return of munti array
         */
        $subCategories = $this->categorySrv->getSubCategoryIdByIdRecursive($categoryId, true, 0, $langId);
        
        foreach ($subCategories As $cat)
        {
            $ctr = 1;
            foreach ($cat['cat_children'] As $key => $val)
            {
                if ($val['cat_status'])
                {
                    // this will control the limit of menu sub header column
                    if ($ctr++ > $limit)
                    {
                        break;
                    }
                    
                    // Sub menu headers
                    $page = array();
                    $page['idPage'] = $pageId;
                    $page['pageType'] = 'Page';
                    $page['pageStat'] = 1;
                    $tmp = $this->categorySrv->getCategoryTranslationById($val['cat_id'], $langId);
                    $label = !empty($tmp->catt_name)? $tmp->catt_name : '';
                    $page['label'] = (!empty($val['catt_name'])) ? $val['catt_name'] : $label;
                    
                    // Generating Category link using MelisComLinkService
                    $page['uri'] = $this->comLinkSrv->getPageLink('category', $val['cat_id'], $langId, false);
                    
                    /**
                     * Adding the sub menu header sub categories
                     */
                    $subPages = array();
                    if (in_array($val['cat_id'], $categoryProductsMenu))
                    {
                        /**
                         * Retreiving Category Products using Product Service
                         * as part of header menu
                         */
                        $catPrds = $this->categorySrv->getCategoryProductsById($val['cat_id'], $langId, true);
                    
                        foreach ($catPrds As $cval)
                        {
                            $prd = $cval->getProduct();
                            
                            if (!empty($prd))
                            {
                                if ($prd->prd_status)
                                {
                                    $product = $this->productSrv->getProductName($cval->getId(), $langId);
                                
                                    $subPage = array();
                                    $subPage['idPage'] = $pageId;
                                    $subPage['pageType'] = 'Page';
                                    $subPage['pageStat'] = 1;
                                    $subPage['label'] = $product;
                                
                                    // Generating Product link using MelisComLinkService
                                    $subPage['uri'] = $this->comLinkSrv->getPageLink('product', $cval->getId(), $langId, false);
                                
                                    array_push($subPages, $subPage);
                                }
                            }
                        }
                    }
                    else
                    {
                        foreach ($val['cat_children'] As $sKey => $sVal)
                        {
                            if ($val['cat_status'])
                            {
                                $subPage = array();
                                $subPage['idPage'] = $pageId;
                                $subPage['pageType'] = 'Page';
                                $subPage['pageStat'] = 1;
                                $label = $this->categorySrv->getCategoryTranslationById($sVal['cat_id'], $langId);
                                
                                $catName = null;
                                if (!empty($label[$langId]))
                                {
                                    $catName = $label[$langId]->catt_name;
                                }
                                else 
                                {
                                    foreach ($label As $cKey => $cnVal)
                                    {
                                        $catName = $cnVal->catt_name;
                                        break;
                                    }
                                }
                                
                                $subPage['label'] = $catName;
                                
                                // Generating Category link using MelisComLinkService
                                $subPage['uri'] = $this->comLinkSrv->getPageLink('category', $sVal['cat_id'], $langId, false);
                                
                                array_push($subPages, $subPage);
                            }
                        }
                    }
                    
                    
                    if (!empty($subPages))
                    {
                        $page['pages'] = $subPages;
                    }
                    
                    array_push($pages, $page);
                }
            }
            
            break;
        }
        
        return $pages;
    }
    
    /**
     * This method will return a list of recent News by months
     * News group by month and get only the recent 4 months from the current date
     *
     * @param int $newsId, Id of the News page create on MelisCms
     * @param int $limit, to limit the subpage, set on config.MelisDemoCommerce.php
     *
     * @return Array
     */
    public function getNewsListFormMenu($newsId, $limit = null)
    {
        /** @var MelisSiteConfigService $siteConfigSrv */
        $siteConfigSrv = $this->getServiceManager()->get('MelisSiteConfigService');

        // Getting the Page Id where the News Details will render
        $newsDetailsIdPage = $siteConfigSrv->getSiteConfigByKey('news_details_page_id', $newsId);
        $siteId = $siteConfigSrv->getSiteConfigByKey('site_id', $newsId);
        /**
         * Retriving the List of Recent 4 News, this is group by month
         * Sample result :
         *      array(
         *          'month' => 1,
         *          'year' => 2017,
         *      ),
         *      array(
         *          'month' => 12,
         *          'year' => 2016,
         *      ),
         *      array(
         *          'month' => 11,
         *          'year' => 2016,
         *      ),
         *      array(
         *          'month' => 10,
         *          'year' => 2016,
         *      ),
         */
        $newsTable = $this->getServiceManager()->get('MelisCmsNewsTable');
        $newsList = $newsTable->getNewsListByMonths(4, $siteId)->toArray();
    
        // Page Tree service to generate Page Link
        $melisTree = $this->getServiceManager()->get('MelisEngineTree');
    
        $newsListMenu = array();
        foreach ($newsList As $key => $val)
        {
            /**
             * Retrieving the list of the News filter by month and year
             */
            $news = array();
            if(!empty($val['month']) && !empty($val['year'])){
                $news = $newsTable->getNewsByMonthYear($val['month'], $val['year'], $limit, $siteId)->toArray();
            }
            
            $newsSubMenu = array();
            foreach ($news As $nKey => $nVal)
            {
                $uri = $melisTree->getPageLink($newsDetailsIdPage, false);
                $newUri = $uri.'?'.http_build_query(array(
                    // Custom data added to the anchor of the link
                    'newsId' => $nVal['cnews_id']
                ));
                $tmp = array();
                $tmp['idPage'] = $newsDetailsIdPage;
                $tmp['pageType'] = 'Page';
                $tmp['pageStat'] = 1;
                $tmp['label'] = $nVal['cnews_title'];
                $tmp['uri'] = $newUri;
                array_push($newsSubMenu, $tmp);
            }
    
            /**
             * If the news Submenu if empty
             * The Header of the Submenu (level 2) will not included to the News menu submenu
             */
            if (!empty($newsSubMenu))
            {
                // Formating the Date to use as label
                $dateLabel = date('F Y', strtotime('01-'.$val['month'].'-'.$val['year']));
    
                $uri = $melisTree->getPageLink($newsId, 1);
                $newUri = $uri.'?'.http_build_query(array(
                    // Custom data added to the anchor of the link
                    'datefilter' => date('Y-m-d', strtotime($val['year'].'-'.$val['month'].'-01'))
                ));
                $tmp = array();
                $tmp['idPage'] = $newsId;
                $tmp['pageType'] = 'Page';
                $tmp['pageStat'] = 1;
                $tmp['label'] = $dateLabel;
                $tmp['uri'] = $newUri;
                $tmp['pages'] = $newsSubMenu;
    
                // Pushing the menuData to the newsListMenu as return
                array_push($newsListMenu, $tmp);
            }
        }
    
        return $newsListMenu;
    }
    
    /**
     * This method will return News list by year and months
     * @param $datefilter if specified this date will be set as selected
     * 
     * @return Array
     */
//    public function getNewsListMonthsYears($datefilter = null)
//    {
//        // Getting the Site config "MelisDemoCommerce.config.php"
//        $siteConfig = $this->getServiceManager()->get('config');
//        $siteConfig = $siteConfig['site']['MelisDemoCommerce'];
//        $siteDatas = $siteConfig['datas'];
//        // Getting the Page Id where the News Details will render
//        $newsIdPage = $siteDatas['news_menu_page_id'];
//
//        /**
//         * Retreiving the the list of News group by months and year
//         */
//        $newsTable = $this->getServiceManager()->get('MelisCmsNewsTable');
//        $newsList = $newsTable->getNewsListByMonths(null, $siteDatas['site_id']);
//
//        $list = array();
//        $addedYear = array();
//
//        foreach ($newsList As $key => $val)
//        {
//            // Checking if the year is already created for groupOption
//            if (!in_array($val->year, $addedYear))
//            {
//                $optgroup = array(
//                    'id' => $newsIdPage,
//                    'text' => $val->year,
//                    'type' => 'optgroup'
//                );
//
//                // Adding the year groupOption using the year as index
//                $list[$val->year] = $optgroup;
//
//                // Adding the year to the existing groupOption
//                array_push($addedYear, $val->year);
//            }
//
//            // Creating option for each group
//            $dataDate = date('Y-m-d', strtotime($val->year.'-'.$val->month.'-01'));
//            $option = array(
//                'id' => $newsIdPage,
//                'text' => date('F', strtotime('01-'.$val->month.'-'.$val->year)),
//                'opt-data' => array( // Custom data added to the anchor of the link
//                    'datefilter' => $dataDate
//                ),
//                'type' => 'option',
//                'selected' => false
//            );
//
//            /**
//             * Checking if the date is match to the selected date
//             * if the date match to the datefilter this date will set as select
//             */
//            if (!is_null($datefilter))
//            {
//                if ($dataDate ==  $datefilter)
//                {
//                    $option['selected'] = true;
//                }
//            }
//
//            // Adding the option of the year
//            $list[$val->year]['options'][] = $option;
//        }
//
//        /**
//         * Sorting the months of each year to asc
//         */
//        foreach ($list As $key => $val)
//        {
//            rsort($list[$key]['options']);
//        }
//
//        return $list;
//    }
    
    /**
     * This method will customize the breadcrumb on the Product page
     * @param array $breadcrumb
     * @param int $productPageId
     * @param int $langId
     * @param array $siteConfigCatalogue
     * @return array
     */
    public function customizeSiteBreadcrumb($breadcrumb, $productPageId, $langId, $siteConfigCatalogue, $productId)
    {
        // Product Page link using MelisComLinksService
        $comLinkSrv = $this->getServiceManager()->get('MelisComLinksService');
        $productUri = $comLinkSrv->getPageLink('product', $productId, $langId, false);
        
        if (!empty($productId))
        {
            /**
             * Retrieving Categeries related to the selected product
             * using the Product Service
             */
            $productSrv = $this->getServiceManager()->get('MelisComProductService');
            $prdCat = $productSrv->getProductCategories($productId);
            
            $catSrv = $this->getServiceManager()->get('MelisComCategoryService');
            
            if (!empty($prdCat))
            {
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
                                $category = $val;
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
                }
                
                /**
                 * Retrieving the Parent Categories 
                 * from top to root 
                 */
                $categories = array();
                $categories = $catSrv->getParentCategory($category->cat_father_cat_id, $categories, false, $langId);
                
                if (!empty($categories))
                {
                    // Sorting the result
                    asort($categories);
                }
                
                // Adding the Primary category of the product as the last data of the array
                array_push($categories, $category->getArrayCopy());
                
                /**
                 * Removing the last data of the Breadcrumb 
                 * which is the name of the Product page
                 * and replace by custom data
                 */
                foreach ($breadcrumb As $key => $val)
                {
                    if ($val['idPage'] == $productPageId)
                    {
                        unset($breadcrumb[$key]);
                    }
                }
                
                //Catalogue page id where products are listed
                $catloguePageId = null;
                
                foreach ($categories As $key => $val)
                {
                    /**
                     * Getting the Page Id to use for generating link of the Category
                     */
                    if (is_null($catloguePageId))
                    {
                        foreach ($siteConfigCatalogue As $cKey => $cVal)
                        {
                            if ($cVal['category_id'] == $val['cat_id'])
                            {
                                $catloguePageId = $cVal['page_id'];
                                break;
                            }
                        }
                    }
                    
                    // Getting the Category name using category service
                    $label =  (!empty($val['catt_name'])) ? $val['catt_name'] : $catSrv->getCategoryNameById($val['cat_id'], $langId);
                    
                    // Validating if the category id has value, else this will not clickable/ no link
                    $flag = 1;
                    $categoryPageUri = null;
                    if (!is_null($catloguePageId))
                    {
                        $flag = 0;
                        // Generating Product link using MelisComLinkService
                        $comLinkSrv = $this->getServiceManager()->get('MelisComLinksService');
                        $categoryPageUri = $comLinkSrv->getPageLink('category', $val['cat_id'], $langId, false);
                    }
                    
                    // Gererating the datas for breadcrumb
                    $tmp = array(
                        'label' => $label,
                        'menu' => 'LINK',
                        'uri' => $categoryPageUri,
                        'idPage' => $productPageId,
                        'lastEditDate' => null,
                        'pageStat' => 1,
                        'pageType' => 'PAGE',
                        'isActive' => $flag,
                    );
                    
                    array_push($breadcrumb, $tmp);
                }
                
                /**
                 * Retrieving Product data from Product Service
                 * and append to the last data of the breadcrub
                 * to show the name of the product
                 */
                $productEntity = $productSrv->getProductById($productId);
                $product = $productEntity->getProduct();
                
                if (!empty($product))
                {
                    $label = $productSrv->getProductName($productId, $langId);
                    $tmp = array(
                        'label' => $label,
                        'menu' => 'LINK',
                        'uri' => $productUri,
                        'idPage' => $productPageId,
                        'lastEditDate' => null,
                        'pageStat' => 1,
                        'pageType' => 'PAGE',
                        'isActive' => 1,
                    );
                    
                    array_push($breadcrumb, $tmp);
                }
            }
        }
        
        return $breadcrumb;
    }
    
    public function customizeSiteBreadcrumbCategory($breadCrumb, $lastPageId, $langId, $siteConfigCatalogue, $categoryId)
    {
        // Category Page link using MelisComLinksService
        $comLinkSrv = $this->getServiceManager()->get('MelisComLinksService');
        $newCrumbs = array();
        $found = false;
        // check if the category is existing in a page
        foreach($siteConfigCatalogue as $key => $val){
            if($categoryId == $val['category_id']){
                $found = true;
                foreach($breadCrumb as $c){
                    if($c['idPage'] == $val['page_id']){
                        $c['uri'] = $comLinkSrv->getPageLink('category', $val['category_id'], $langId, false);
                    }
                    $newCrumbs[] = $c;
                }
            }
        }
        
        if(!empty($newCrumbs)){
            $breadCrumb = $newCrumbs;
        }
        
        if(!$found){
            
            $catSrv = $this->getServiceManager()->get('MelisComCategoryService');
            $cat = $catSrv->getCategoryBreadCrumb($categoryId, $langId);
            
            if (!empty($cat))
            {
                $res = $this->customizeSiteBreadcrumbCategory($breadCrumb, $lastPageId, $langId, $siteConfigCatalogue, $cat->cat_father_cat_id);
                
                // reassign active links
                foreach ($res as $crumb){
                    $crumb['isActive'] = 0;
                    $crumbs[] = $crumb;
                }
                
                $breadCrumb = $crumbs;
                
                $categoryPageUri = $comLinkSrv->getPageLink('category', $cat->cat_id, $langId, false);
                
                $last = end($breadCrumb);
                
                $label = !empty($cat->catt_name)? $cat->catt_name : $cat->cat_reference;
                
                $tmp = array(
                    'label' => $label,
                    'menu' => 'LINK',
                    'uri' => $categoryPageUri,
                    'idPage' => $last['idPage'],
                    'lastEditDate' => null,
                    'pageStat' => 1,
                    'pageType' => 'PAGE',
                    'isActive' => 1,
                );
                // add new breadcrumb
                $breadCrumb[] = $tmp;
            }
        }
        return $breadCrumb;
    }
    
    public function getVariantbyAttributes($productId, $attrSelection, $action, $pageId)
    {
        $container = new Container('melisplugins');
        $langId = $container['melis-plugins-lang-id'];

        /** @var MelisSiteConfigService $siteConfigSrv */
        $siteConfigSrv = $this->getServiceManager()->get('MelisSiteConfigService');

        $countryId = $siteConfigSrv->getSiteConfigByKey('site_country_id', $pageId);

        $variant = array();
        $varPrice = array();
        $varStock = array();
        $variantAttr = array();
        
        $variantSrv = $this->getServiceManager()->get('MelisComVariantService');
        
        /**
         * re-initialize attrSelection
         * This will depend on action, this will initialize to empty the greater value
         * of the current action
         * Sample:
         * Posted
         * $action = 1;
         * $attrSelection = array(
         *    '1' => 2,
         *    '2' => 3,
         *    '3' => 4
         *  );
         * Result
         * $attrSelection = array(
         *    '1' => 2,
         *    '2' => '',
         *    '3' => ''
         *  );
         *
         *  The purpose is to select all variant having the first attribute up to last attribut
         */
        foreach ($attrSelection As $key => $val)
        {
            if ($action < $key)
            {
                $attrSelection[$key] = '';
            }
        }
        /**
         * Retrieving Variant that has common attribute to other variant
         * with the same Attribute id and attribute value id
         */
        $variantsAttr = array();
        foreach ($attrSelection As $aKey => $aVal)
        {
            $temp = $variantSrv->getVariantCommonAttr($productId, $aKey, $aVal);
            foreach ($temp As $vKey => $vVey)
            {
                if (!empty($variantsAttr[$aKey]))
                {
                    if (!in_array($vVey->var_id, $variantsAttr[$aKey]))
                    {
                        array_push($variantsAttr[$aKey], $vVey->var_id);
                    }
                }
                else
                {
                    $variantsAttr[$aKey][] = $vVey->var_id;
                }
            }
        }

        /**
         * merging results of variants ids
         * In this case this will only merge that match from other array
         */
        $variantsIds = array();
        foreach ($attrSelection As $key => $val)
        {
            if (empty($variantsIds))
            {
                $variantsIds = $variantsAttr[$key];
            }
            else
            {
                $variantsIds = array_intersect($variantsIds, $variantsAttr[$key]);
            }
        }

        /**
         * Retrieving variant the match to the attributes submited
         */
        if(!empty($variantsIds)) {
            $variants = $variantSrv->getVariantsAttrGroupByAttr($variantsIds);
            foreach ($variants As $val) {
                if (!isset($variantAttr[$val->attr_id])) {
                    $variantAttr[$val->attr_id]['selected'] = (int)$attrSelection[$val->attr_id];
                    $variantAttr[$val->attr_id]['selections'][] = (int)$val->vatv_attribute_value_id;
                } else {
                    $variantAttr[$val->attr_id]['selections'][] = (int)$val->vatv_attribute_value_id;
                }
            }
        }

        //if (count(array_filter($attrSelection)) == count($attrSelection))
        //{
            if (!empty($variantsIds))
            {
                sort($variantsIds);

                // Client group
                $ecomAuthSrv = $this->getServiceManager()->get('MelisComAuthenticationService');
                $clientGroup = null;
                if ($ecomAuthSrv->hasIdentity())
                    $clientGroup = $ecomAuthSrv->getClientGroup();
                
                // Getting the Variant from Variant Service Entity
                $variant = $variantSrv->getVariantById($variantsIds[0], $langId);
                
                // Getting the Final Price of the variant
                $varPrice = $variantSrv->getVariantFinalPrice($variantsIds[0], $countryId, $clientGroup);
                if (empty($varPrice))
                {
                    $productSrv = $this->getServiceManager()->get('MelisComProductService');
                    // If the variant price not set on variant page this will try to get from the Product Price
                    $varPrice = $productSrv->getProductFinalPrice($productId, $countryId, $clientGroup);
                    if(empty($varPrice)) {
                        //get price form general group in product
                        $varPrice = $productSrv->getProductFinalPrice($productId, $countryId);
                    }
                }
                
                // Getting Variant stock
                $varStock = $variantSrv->getVariantFinalStocks($variantsIds[0], $countryId);
                if ($varStock)
                {
                    
                    $basketSrv = $this->getServiceManager()->get('MelisComBasketService');
                    
                    $clientKey = $ecomAuthSrv->getId();
                    $clientId = null;
                    if ($ecomAuthSrv->hasIdentity())
                    {
                        $clientId = $ecomAuthSrv->getClientId();
                        $clientKey = $ecomAuthSrv->getClientKey();
                    }
                    
                    $currentQty = 0;
                    
                    $currentBasket = $basketSrv->getBasket($clientId, $clientKey);
                    if(!empty($currentBasket)){
                        foreach($currentBasket as $item){
                            if($item->getVariantId() == $variantsIds[0]){
                                $currentQty = $item->getQuantity();
                            }
                        }
                    }
                    $varStock->stock_quantity = $varStock->stock_quantity - $currentQty;
                    
                    $countryId = $varStock->stock_country_id;
                }
            }
        //}
        
        $result = array(
            'variant' => ($variant) ? $variant->getVariant() : array(),
            'countryId' => $countryId,
            'variant_price' => $varPrice,
            'variant_attr' => $variantAttr,
            'variant_stock' => $varStock,
        );
        
        return $result;
    }
}