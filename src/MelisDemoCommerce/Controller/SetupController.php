<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2015 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisDemoCommerce\Controller;

use MelisFront\Controller\MelisSiteActionController;

use Laminas\Db\Adapter\Adapter as DbAdapter;
use Laminas\Session\Container;
use Laminas\View\Helper\ViewModel;
use Laminas\View\Model\JsonModel;
use Laminas\Stdlib\ArrayUtils;

class SetupController extends MelisSiteActionController
{
    private $isTest = false;
    private $siteId;
    private $mainPageId;
    private $curPlatformId;
    private $tplIds = array();
    private $pagesIds = array();
    private $configDir;
    private $config;
    private $categoryIds = array();
    private $catTranslations = array();
    private $catProducts = array();
    private $catDocumentRelations = array();
    private $docTypeIds = array();
    private $attrIds = array();
    private $attrTypeIds = array();
    private $attrTypeColValIds = array();
    private $attrValuesIds = array();
    private $productTextTypeIds = array();
    private $productIds = array();
    private $variantIds = array();
    private $couponIds = array();
    
    
    public function __construct()
    {
        $this->mediaDirPermissionCheck();
        $this->siteConfigCheck();
        set_time_limit (1000);
    }
    
    public function setupAction()
    {
        $this->layout('MelisDemoCommerce/setupLayout');
        $view = new ViewModel();
        
        $tablePlatform = $this->getServiceManager()->get('MelisPlatformTable');
        $platform = $tablePlatform->getEntryByField('plf_name', getenv('MELIS_PLATFORM'))->current();
        
        if (empty($platform))
            exit('Current Platform "'.getenv('MELIS_PLATFORM').'" has no data on database.');

        $platformIdsTbl = $this->getServiceManager()->get('MelisEngineTablePlatformIds');
        if (empty($platformIdsTbl->getEntryById($platform->plf_id)->current()))
            exit('Current Platform "'.getenv('MELIS_PLATFORM').'" has no Platform ID\'s to be used.');

        $setupSessTmp = new Container('MelisDemoCommerce');
        if (!isset($setupSessTmp['reload-path'])) {
            // Updating modules path config
            unlink($_SERVER['DOCUMENT_ROOT'].'/../config/melis.modules.path.php');
            // declare the index
            $setupSessTmp['reload-path'] = 1;
            // reload
            return $this->redirect()->toUrl('/MelisDemoCommerce/setup');
        }

        return $view;
    }
    
    public function executeSetupAction()
    {
        $nextStep = '';
        $done = 0;
        
        $request = $this->getRequest();
        
        if ($request->isPost())
        {
            // Getting the DemoSite config
            $melisSite = $_SERVER['DOCUMENT_ROOT'].'/../module/MelisSites';
            $outputFileName = 'MelisDemoCommerce.config.php';
            $this->configDir = $melisSite.'/MelisDemoCommerce/config/'.$outputFileName;
            
            $tablePlatform = $this->getServiceManager()->get('MelisPlatformTable');
            $platform = $tablePlatform->getEntryByField('plf_name', getenv('MELIS_PLATFORM'))->current();

            if ($platform)
            {
                $this->curPlatformId = $platform->plf_id;
            
                $post = $request->getPost()->toArray();
                $step = $post['step'];
                
                $this->config = file_get_contents($this->configDir);
                try {
                    switch ($step) {
                        case 'templates' :
                            $setupDatas = include __DIR__ . '../../../../install/MelisDemoCommerce.' . $step . '.setup.php';
                            $this->setTemplates($setupDatas);
                            $nextStep = 'pages';
                            break;
                        case 'pages' :
                            $setupDatas = include __DIR__ . '../../../../install/MelisDemoCommerce.' . $step . '.setup.php';
                            $this->setupPages($setupDatas);
                            $nextStep = 'sliders';
                            break;
                        case 'sliders' :
                            $setupDatas = include __DIR__ . '../../../../install/MelisDemoCommerce.' . $step . '.setup.php';
                            $this->setupSliders($setupDatas);
                            $nextStep = 'news';
                            break;
                        case 'news' :
                            $setupDatas = include __DIR__ . '../../../../install/MelisDemoCommerce.' . $step . '.setup.php';
                            $this->setupNews($setupDatas);
                            $nextStep = 'prospects_theme';
                            break;
                        case 'prospects_theme' :
                            $setupDatas = include __DIR__ . '../../../../install/MelisDemoCommerce.' . $step . '.setup.php';
                            $this->setupProspectsThemes($setupDatas);
                            $nextStep = 'document_types';
                            break;
                        case 'document_types' :
                            $setupDatas = include __DIR__ . '../../../../install/MelisDemoCommerce.' . $step . '.setup.php';
                            $this->setupDocumentTypes($setupDatas);
                            $nextStep = 'attributes';
                            break;
                        case 'attributes' :
                            $setupDatas = include __DIR__ . '../../../../install/MelisDemoCommerce.' . $step . '.setup.php';
                            $this->setupAttibutes($setupDatas);
                            $nextStep = 'product_text_types';
                            break;
                        case 'product_text_types' :
                            $setupDatas = include __DIR__ . '../../../../install/MelisDemoCommerce.' . $step . '.setup.php';
                            $this->setupProductTextTypes($setupDatas);
                            $nextStep = 'products';
                            break;
                        case 'products' :
                            $setupDatas = include __DIR__ . '../../../../install/MelisDemoCommerce.' . $step . '.setup.php';
                            $this->setupProducts($setupDatas);
                            $nextStep = 'categories';
                            break;
                        case 'categories' :
                            $setupDatas = include __DIR__ . '../../../../install/MelisDemoCommerce.' . $step . '.setup.php';
                            $this->setupCategories($setupDatas);
                            $this->setupCategoriesIds();
                            $nextStep = 'variants';
                            break;

                        case 'variants' :
                            $setupDatas = include __DIR__ . '../../../../install/MelisDemoCommerce.' . $step . '.setup.php';
                            $this->setupVariants($setupDatas);
                            $nextStep = 'coupons';
                            break;
                        case 'coupons' :
                            $setupDatas = include __DIR__ . '../../../../install/MelisDemoCommerce.' . $step . '.setup.php';
                            $this->setupCoupon($setupDatas);
                            $nextStep = 'client_and_orders';
                            break;
                        case 'client_and_orders' :
                            $setupDatas = include __DIR__ . '../../../../install/MelisDemoCommerce.' . $step . '.setup.php';
                            $this->setupClientAndOrders($setupDatas);
                            $nextStep = 'setup_main_page';
                            break;
                        case 'setup_main_page' :
                            $container = new Container('MelisDemoCommerceSetup');
                            $siteId = $container['MelisDemoCommerceSetup']['siteId'];
                            $mainPageId = $container['MelisDemoCommerceSetup']['mainPageId'];
                            // Update the main page id of the Site created for MelisDemoSite
                            $this->setupSite(array('site_main_page_id' => $mainPageId), $siteId);
                            $done = 1;
                            break;
                        default:
                            $container = new Container('MelisDemoCommerceSetup');
                            $container['MelisDemoCommerceSetup'] = array();

                            if (!empty($post['protocol']) && !empty($post['domain'])) {
                                $container['MelisDemoCommerceSetup']['protocol'] = $post['protocol'];
                                $container['MelisDemoCommerceSetup']['domain'] = $post['domain'];
                            }

                            $setupDatas = include __DIR__ . '../../../../install/MelisDemoCommerce.site.setup.php';
                            # Site label
                            $siteLabel = !empty($post['site_label']) ? $post['site_label'] : null;
                            if (!empty($siteLabel)) $setupDatas['site_label'] = $siteLabel;
                            # Start setup
                            $this->setupSite($setupDatas);
                            $nextStep = 'templates';
                            break;
                    }
                }catch(\Exception $ex){
                    exit($ex->getMessage());
                }
                file_put_contents($this->configDir, $this->config);
            }
        }
        
        $response = array(
            'nextStep' => $nextStep,
            'done' => $done
        );
        
        return new JsonModel($response);
    }
    
    private function siteConfigCheck()
    {
        // Getting the DemoSite config
        $melisSite = $_SERVER['DOCUMENT_ROOT'].'/../module/MelisSites';
        $outputFileName = 'MelisDemoCommerce.config.php';
        $configDir = $melisSite.'/MelisDemoCommerce/config/'.$outputFileName;
        $moduleConfigFileName = 'module.config.php';
        $moduleConfigDir = $melisSite.'/MelisDemoCommerce/config/'.$moduleConfigFileName;
        
        if (!is_writable($configDir) || !is_writable($moduleConfigDir))
        {
            exit('Access permission denied, Please make /MelisDemoCommerce/config/'.$outputFileName.' and /MelisDemoCommerce/config/'.$moduleConfigFileName.' files writable');
        }
        
    }
    
    /**
     * Checking and creating directories for Documents
     * if the directory no exist this will try to create
     * else Permission need to generate the Directory
     */
    private function mediaDirPermissionCheck()
    {
        if (is_dir($_SERVER['DOCUMENT_ROOT'].'/'.'media/'))
        {
            if (is_writable($_SERVER['DOCUMENT_ROOT'].'/'.'media/'))
            {
                if (is_dir($_SERVER['DOCUMENT_ROOT'].'/'.'media/commerce/'))
                {
                    if (!is_writable($_SERVER['DOCUMENT_ROOT'].'/'.'media/commerce/'))
                    {
                        exit('Access permission denied, Please make /public/media/commerce/ directory writable');
                    }
                }
                else 
                {
                    mkdir($_SERVER['DOCUMENT_ROOT'].'/'.'media/commerce', 0777);
                }
            }
            else
            {
                exit('Access permission denied, Please make /public/media directory writable');
            }
        }
        else
        {
            if (is_writable($_SERVER['DOCUMENT_ROOT'].'/'))
            {
                mkdir($_SERVER['DOCUMENT_ROOT'].'/'.'media/', 0777);
                mkdir($_SERVER['DOCUMENT_ROOT'].'/'.'media/commerce/', 0777);
            }
            else 
            {
                exit('Directory /public/media is not yet created, please create /public/media directory with writable access permission.');
            }
        }
    }
    
    
    /**
     * Create entry to melis_cms_site for MelisDemoCoomerce
     * @param array $site
     * @param array $siteId
     */
    private function setupSite($site, $siteId = null)
    {
        $container = new Container('MelisDemoCommerceSetup');
        
        $siteTbl = $this->getServiceManager()->get('MelisEngineTableSite');
        
        if (is_null($siteId))
        {
            /**
             * Set Site main page id to temporary value, 
             * cuase main page of MelisDemoCommerce is not yet create
             */
            $site['site_main_page_id'] = '-1';
        }
        $siteTblCols  = $siteTbl->getTableColumns();
        # Check site_label column is present
        if (in_array('site_label',$siteTblCols)) {
            # Then save
            $this->siteId = $siteTbl->save($site, $siteId);
        } else {
            # remove site_label key
            unset($site['site_label']);
            $this->siteId = $siteTbl->save($site, $siteId);
        }

        $container['MelisDemoCommerceSetup']['siteId'] = $this->siteId;
        
        if (is_null($siteId))
        {
            $this->config = str_replace('\'[:siteId]\'', $this->siteId, $this->config);
            $this->setupSiteDomain();

            //site lang
            $this->setupSiteLang();
        }
    }
    
    private function setupSiteDomain()
    {
        $container = new Container('MelisDemoCommerceSetup');
        
        $siteDomainTbl = $this->getServiceManager()->get('MelisEngineTableSiteDomain');
        $siteDomain = array(
            'sdom_site_id' => $container['MelisDemoCommerceSetup']['siteId'],
            'sdom_env' => getenv('MELIS_PLATFORM'),
            'sdom_scheme' => $container['MelisDemoCommerceSetup']['protocol'],
            'sdom_domain' => $container['MelisDemoCommerceSetup']['domain'],
        );
        
        $this->saveData($siteDomainTbl, $siteDomain);
    }

    /**
     * Save site lang data
     */
    private function setupSiteLang()
    {
        $container = new Container('MelisDemoCommerceSetup');
        $sitelangsTable = $this->getServiceManager()->get('MelisEngineTableCmsSiteLangs');
        $siteLangData = array(
            'slang_site_id' => $container['MelisDemoCommerceSetup']['siteId'],
            'slang_lang_id' => 1,
        );
        $this->saveData($sitelangsTable, $siteLangData);
    }
    
    /**
     * Create entry to melis_cm_templates for MelisDemoCommerce
     * @param array $templates
     */
    private function setTemplates($templates)
    {
        $container = new Container('MelisDemoCommerceSetup');
        
        $tplTbl = $this->getServiceManager()->get('MelisEngineTableTemplate');
        $platformIdsTbl = $this->getServiceManager()->get('MelisEngineTablePlatformIds');
    
        foreach ($templates As $tpl)
        {
            $platformIds = $platformIdsTbl->getEntryById($this->curPlatformId)->current();
            $tplId = $platformIds->pids_tpl_id_current;
            
            $tpl['tpl_id'] = $tplId;
            $tpl['tpl_site_id'] = $container['MelisDemoCommerceSetup']['siteId'];
            $tpl['tpl_creation_date'] = date('Y-m-d H:i:s');
            $this->saveData($tplTbl, $tpl);
    
            $this->tplIds[$tpl['tpl_zf2_controller'].ucfirst($tpl['tpl_zf2_action'])] = $tplId;
    
            $platformIdsTbl->save(array('pids_tpl_id_current' => ++$tplId), $platformIds->pids_id);
        }
        
        $container['MelisDemoCommerceSetup']['tplIds'] = $this->tplIds;
    }
    
    /**
     * Create entry for melis_cms_page_published, melis_cms_page_saved,
     * melis_cms_page_lang, melis_cms_page_tree for MelisDemoCommerce
     * @param array $pages
     * @param int $fatherId
     */
    private  function setupPages($pages, $fatherId = -1)
    {
        $container = new Container('MelisDemoCommerceSetup');
        
        $pageTreeTbl = $this->getServiceManager()->get('MelisEngineTablePageTree');
        $pageLangTbl = $this->getServiceManager()->get('MelisEngineTablePageLang');
        $pageSavedTbl = $this->getServiceManager()->get('MelisEngineTablePageSaved');
        $pagePublishedTbl = $this->getServiceManager()->get('MelisEngineTablePagePublished');
        $platformIdsTbl = $this->getServiceManager()->get('MelisEngineTablePlatformIds');
        $melisTablePageSeo = $this->getServiceManager()->get('MelisEngineTablePageSeo');
        $melisTableSite404 = $this->getServiceManager()->get('MelisEngineTableSite404');
        $siteHomeTable = $this->getServiceManager()->get('MelisEngineTableCmsSiteHome');

        // Getting the DemoSite config
        $melisSite = $_SERVER['DOCUMENT_ROOT'].'/../module/MelisSites';
        $outputFileName = 'module.config.php';
        $moduleConfigDir = $melisSite.'/MelisDemoCommerce/config/'.$outputFileName;
        $modulePluginsConfigDir = $melisSite.'/MelisDemoCommerce/config/melis.plugins.config.php';
        
        $moduleConfig = file_get_contents($moduleConfigDir);

        $pageOrder = 1;
        foreach ($pages As $page)
        {
            // Retrieving Next id for Page on Plaform Ids
            $platformIds = $platformIdsTbl->getEntryById($this->curPlatformId)->current();
            $tmpPageId = $platformIds->pids_page_id_current;
            // Adding the page id to pages ids array
            $this->pagesIds[strtolower($page['columns']['page_name']).'PageId'] = $tmpPageId;
            
            $page['columns']['page_id'] = $tmpPageId;
            $page['columns']['page_taxonomy'] = '';
            $page['columns']['page_creation_date'] = date('Y-m-d H:i:s');
            $page['columns']['page_tpl_id'] = ($page['columns']['page_tpl_id'] != '-1') ? $container['MelisDemoCommerceSetup']['tplIds'][$page['columns']['page_tpl_id']] : '-1';
            unset($this->tplIds[$page['columns']['page_tpl_id']]);
            
            if ($page['page_type'] == 'published')
            {
                // Save page to page published table
                $this->saveData($pagePublishedTbl, $page['columns']);
            }
            else
            {
                // Save page to page saved table
                $this->saveData($pageSavedTbl, $page['columns']);
            }
    
            // page tree
            $pageTree = array(
                'tree_page_id' => $tmpPageId,
                'tree_father_page_id' => $fatherId,
                'tree_page_order' => $pageOrder++
            );
            $this->saveData($pageTreeTbl, $pageTree);
    
            // page lang
            $pageLang = array(
                'plang_page_id' => $tmpPageId,
                'plang_lang_id' => 1,
                'plang_page_id_initial' => $tmpPageId,
            );
            $this->saveData($pageLangTbl, $pageLang);

            /**
             * insert the site 404 data
             */
            if($page['columns']['page_name'] == '404') {
                //site 404
                $site404Data = array(
                    's404_site_id' => $container['MelisDemoCommerceSetup']['siteId'],
                    's404_page_id' => $tmpPageId,
                );
                $this->saveData($melisTableSite404, $site404Data);
            }

            if (!empty($page['is_main_page']))
            {
                $this->mainPageId = $tmpPageId;
                $container['MelisDemoCommerceSetup']['mainPageId'] = $this->mainPageId;
                $this->config = str_replace('\'[:homePageId]\'', $tmpPageId, $this->config);

                //insert site home data
                $siteHomeData = array(
                    'shome_site_id' => $container['MelisDemoCommerceSetup']['siteId'],
                    'shome_lang_id' => 1,
                    'shome_page_id' => $tmpPageId
                );
                $this->saveData($siteHomeTable, $siteHomeData);
            }
            
            if (!empty($page['site_config']))
            {
                $this->config = str_replace('\'[:'.$page['site_config'].']\'', $tmpPageId, $this->config);
                $moduleConfig = str_replace('\'[:'.$page['site_config'].']\'', $tmpPageId, $moduleConfig);
                //update plugins module config
                if (strpos(file_get_contents($modulePluginsConfigDir), '\'[:'.$page['site_config'].']\'') !== false) {
                    $modulePluginsConfig = preg_replace('(\'\[:'.$page['site_config'].'\]\')', $tmpPageId, file_get_contents($modulePluginsConfigDir));
                    file_put_contents($modulePluginsConfigDir, $modulePluginsConfig);
                }
            }
            
            // Page SEO
            if (!empty($page['seo']))
            {
                $page['seo']['pseo_id'] = $tmpPageId;
                $melisTablePageSeo->save($page['seo']);
            }
            
            // Updating the CMS Platform Ids
            $platformIdsTbl->save(array('pids_page_id_current' => ($tmpPageId + 1)), $platformIds->pids_id);
    
            if (!empty($page['page_subpages']))
            {
                $this->setupPages($page['page_subpages'], $tmpPageId);
            }
        }
        file_put_contents($moduleConfigDir, $moduleConfig);



        $container['MelisDemoCommerceSetup']['pagesIds'] = $this->pagesIds;
    }
    
    /**
     * Create entry for melis_cms_slider and melis_cms_slider_details for MelisDemoCommerce
     * @param array $sliders
     */
    private  function setupSliders($sliders)
    {
        $sliderTbl = $this->getServiceManager()->get('MelisCmsSliderTable');
        $sliderDetailsTbl = $this->getServiceManager()->get('MelisCmsSliderDetailTable');
        
        foreach ($sliders As $slider)
        {
            $slider['columns']['mcslide_date'] = date('Y-m-d H:i:s');
            $sliderId = $this->saveData($sliderTbl, $slider['columns']);
            
            if (!empty($slider['site_config']))
            {
                $this->config = str_replace('\'[:'.$slider['site_config'].']\'', $sliderId, $this->config);
            }
            
            if (!empty($slider['slider_details']))
            {
                $sliderOrder = 1;
                foreach ($slider['slider_details'] As $sliderDetail)
                {
                    $sliderDetail['mcsdetail_mcslider_id'] = $sliderId;
                    $sliderDetail['mcsdetail_order'] = $sliderOrder++;
                    $this->saveData($sliderDetailsTbl, $sliderDetail);
                }
            }
        }
    }
    
    /**
     * Create entry for melis_cms_news for MelisDemoCommerce
     * @param array $news
     */
    private function setupNews($news)
    {
        $container = new Container('MelisDemoCommerceSetup');
        $newsTbl = $this->getServiceManager()->get('MelisCmsNewsTable');
        $newsTxtTbl = $this->getServiceManager()->get('MelisCmsNewsTextsTable');
        
        $ctr = 0;
        $monthCtr = 0;
        foreach ($news As $val)
        {
            $val['cnews_status'] = 1;
            $val['cnews_publish_date'] = date('Y-m-d H:i:s', strtotime(' - '.$monthCtr.' month'));
            $val['cnews_site_id'] = $container['MelisDemoCommerceSetup']['siteId'];
            
            $newsText = array();
            if (isset($val['cnews_texts']))
            {
                $newsText = $val['cnews_texts'];
                unset($val['cnews_texts']);
            }
            
            $newId = $this->saveData($newsTbl, $val);
            
            // insert text data
            $newsText['cnews_id'] = $newId;
            $newsText['cnews_lang_id'] = 1;
            $this->saveData($newsTxtTbl, $newsText);
            
            if (++$ctr == 4)
            {
                $ctr = 0;
                $monthCtr++;
            }
        }
    }
    
    /**
     * Creating entry for melis_cms_prospects_themes and melis_cms_prospects_theme_items
     * @param array $items
     */
    private function setupProspectsThemes($items)
    {
        $themeTbl = $this->getServiceManager()->get('MelisCmsProspectsThemeTable');
        $themeItemTbl = $this->getServiceManager()->get('MelisCmsProspectsThemeItemTable');
        $themeItemTransTbl = $this->getServiceManager()->get('MelisCmsProspectsThemeItemTransTable');
        
        foreach ($items As $val)
        {
            $themItems = $val['pros_theme_items_trans'];
            unset($val['pros_theme_items_trans']);
            $themeId = $themeTbl->save($val);
                        
            foreach ($themItems As $vItems)
            {
                $themItemData = array(
                    'pros_theme_id' =>  $themeId 
                );
                
                $itemId = $themeItemTbl->save($themItemData);
                
                $vItems['item_trans_theme_item_id'] = $itemId;
                
                $themeItemTransTbl->save($vItems);
                
            }
        }
    }
    
    /**
     * Create entry for 
     * @param array $docTypes
     */
    private function setupDocumentTypes($docTypes)
    {
        $container = new Container('MelisDemoCommerceSetup');
        $docTypeTbl = $this->getServiceManager()->get('MelisEcomDocTypeTable');
        
        foreach ($docTypes As $key => $val)
        {
            $docType = $docTypeTbl->getEntryByField('dtype_code', $val['dtype_code'])->current();
            if (!empty($docType))
            {
                $this->docTypeIds[$docType->dtype_code] = $docType->dtype_id;
            }
            else
            {
                $docTypeId = $this->saveData($docTypeTbl, $val);
                $this->docTypeIds[$val['dtype_code']] = $docTypeId;
            }
        }
        
        $container['MelisDemoCommerceSetup']['docTypeIds'] = $this->docTypeIds;
    }
    
    /**
     * Create entry for 
     * @param array $attributeTypes
     */
    private function getAttributeTypes()
    {
        $container = new Container('MelisDemoCommerceSetup');
        
        $attrTypeTable = $this->getServiceManager()->get('MelisEcomAttributeTypeTable');
        
        foreach ($attrTypeTable->fetchAll()->toArray() As $val)
        {
            $this->attrTypeIds[$val['atype_name']] = $val['atype_id'];
            $this->attrTypeColValIds[$val['atype_name']] = $val['atype_column_value'];
        }
        
        $container['MelisDemoCommerceSetup']['attrTypeIds'] = $this->attrTypeIds;
        $container['MelisDemoCommerceSetup']['attrTypeColValIds'] = $this->attrTypeColValIds;
    }
    
    /**
     * 
     * @param array $attributes
     */
    private function setupAttibutes($attributes)
    {
        $container = new Container('MelisDemoCommerceSetup');
        
        $attrTable = $this->getServiceManager()->get('MelisEcomAttributeTable');
        $attrTransTable = $this->getServiceManager()->get('MelisEcomAttributeTransTable');
        $attrValTable = $this->getServiceManager()->get('MelisEcomAttributeValueTable');
        $attrValTransTable = $this->getServiceManager()->get('MelisEcomAttributeValueTransTable');
        
        $this->getAttributeTypes();
        
        $attrValTranslations = array();
        
        foreach ($attributes As $val)
        {
            $attr = $val['columns'];
            $attr['attr_type_id'] = $container['MelisDemoCommerceSetup']['attrTypeIds'][$attr['attr_type_id']];
            
            $attrId = $this->saveData($attrTable, $attr);
            
            $this->attrIds[$attr['attr_reference']] = $attrId;
            
            $attrTrans = $val['attribute_trans'];
            foreach ($attrTrans As $atVal)
            {
                $attrTrans = $atVal;
                $attrTrans['atrans_attribute_id'] = $attrId;
                $this->saveData($attrTransTable, $attrTrans);
            }
            
            $attrValues = $val['attribute_values'];
            foreach ($attrValues As $avVal)
            {
                $attValue = $avVal['columns'];
                $attValue['atval_attribute_id'] = $attrId;
                $AttrType = $attValue['atval_type_id'];
                $attValue['atval_type_id'] = $container['MelisDemoCommerceSetup']['attrTypeIds'][$AttrType];
                $attrValId = $this->saveData($attrValTable, $attValue);
                
                $attrValuesTrans = $avVal['attribute_value_trans'];
                foreach ($attrValuesTrans As $avtVal)
                {
                    $attrValTrans = $avtVal;
                    $attrValTrans['av_attribute_value_id'] = $attrValId;
                    
                    foreach ($attrValTrans As $avcKey => $avcVal)
                    {
                        if (!$avcVal)
                        {
                            $attrValTrans[$avcKey] = null;
                        }
                    }
//                     $this->saveData($attrValTransTable, $attrValTrans);
                    array_push($attrValTranslations, $attrValTrans);
                    
                    $this->attrValuesIds[$attr['attr_reference'].'_'.$avtVal['avt_v_'.$this->attrTypeColValIds[$AttrType]]] = $attrValId;
                }
            }
            
            if (!empty($val['site_config']))
            {
                $this->config = str_replace('\'[:'.$val['site_config'].']\'', $attrId, $this->config);
            }
        }
        
        if (empty($container['MelisDemoCommerceSetup']['attrIds']))
        {
            $container['MelisDemoCommerceSetup']['attrIds'] = $this->attrIds;
        }
        else 
        {
            $container['MelisDemoCommerceSetup']['attrIds'] = ArrayUtils::merge($container['MelisDemoCommerceSetup']['attrIds'], $this->attrIds);
        }
        
        if (empty($container['MelisDemoCommerceSetup']['attrValuesIds']))
        {
            $container['MelisDemoCommerceSetup']['attrValuesIds'] = $this->attrValuesIds;
        }
        else
        {
            $container['MelisDemoCommerceSetup']['attrValuesIds'] = ArrayUtils::merge($container['MelisDemoCommerceSetup']['attrValuesIds'], $this->attrValuesIds);
        }
        
        
        $this->saveAttributesBatchData($attrValTransTable, $attrValTranslations);
    }
    
    /**
     * Create entry for 
     * @param array $productTxtTypes
     */
    private function setupProductTextTypes($productTxtTypes)
    {
        $container = new Container('MelisDemoCommerceSetup');
        
        $prdTextTypesTbl = $this->getServiceManager()->get('MelisEcomProductTextTypeTable');
        $productTextCodes = array();
        foreach ($prdTextTypesTbl->fetchAll()->toArray() As $val)
        {
            array_push($productTextCodes, $val['ptt_code']);
            $this->productTextTypeIds[$val['ptt_code']] = $val['ptt_id'];
        }
        
        foreach ($productTxtTypes As $val)
        {
            if (!in_array($val['ptt_code'], $productTextCodes))
            {
                $productTextTypeId = $this->saveData($prdTextTypesTbl, $val);
                $this->productTextTypeIds[$val['ptt_code']] = $productTextTypeId;
            }
        }
        
        $container['MelisDemoCommerceSetup']['productTextTypeIds'] = $this->productTextTypeIds;
    }
    
    /**
     * Create entry for 
     * @param array $products
     */
    private function setupProducts($products)
    {
        $container = new Container('MelisDemoCommerceSetup');
        
        $prdTbl = $this->getServiceManager()->get('MelisEcomProductTable');
        $prdCatTbl = $this->getServiceManager()->get('MelisEcomProductCategoryTable');
        $prdAttrTbl = $this->getServiceManager()->get('MelisEcomProductAttributeTable');
        $docRelTbl = $this->getServiceManager()->get('MelisEcomDocRelationsTable');
        $docTbl = $this->getServiceManager()->get('MelisEcomDocumentTable');
        $prdTxtTbl = $this->getServiceManager()->get('MelisEcomProductTextTable');
        $priceTbl = $this->getServiceManager()->get('MelisEcomPriceTable');
        
//         $prdCategories = array();
        $prdAttributes = array();
        $prdDocumentRelations = array();
        $prdTexts = array();
        $prdPrices = array();
        foreach ($products As $val)
        {
            $product = $val['columns'];
            $product['prd_user_id_creation'] = 1;
            $product['prd_date_creation'] = date('Y-m-d H:i:s');
            $productId = $this->saveData($prdTbl, $product);
            
            $this->productIds[$product['prd_reference']] = $productId;
            
//             if (!empty($val['product_categories']))
//             {
//                 $prdCatOrder = 1;
//                 foreach ($val['product_categories'] As $pcVal)
//                 {
//                     $prdCat = $pcVal;
                    
//                     $prdCat['pcat_cat_id'] = (!empty($container['MelisDemoCommerceSetup']['categoryIds'][$prdCat['pcat_cat_id']])) ? $container['MelisDemoCommerceSetup']['categoryIds'][$prdCat['pcat_cat_id']] : $prdCat['pcat_cat_id'];
//                     $prdCat['pcat_prd_id'] = $productId;
//                     $prdCat['pcat_order'] = $prdCatOrder++;
//                     array_push($prdCategories, $prdCat);
//                 }
//             }
            
            if (!empty($val['product_attributes']))
            {
                foreach ($val['product_attributes'] As $paVal)
                {
                    $productAttr = $paVal;
                    $productAttr['patt_product_id'] = $productId;
                    $productAttr['patt_attribute_id'] = (!empty($container['MelisDemoCommerceSetup']['attrIds'][$productAttr['patt_attribute_id']])) ? $container['MelisDemoCommerceSetup']['attrIds'][$productAttr['patt_attribute_id']] : $productAttr['patt_attribute_id'];
                    array_push($prdAttributes, $productAttr);
                }
            }
            
            if (!empty($val['product_documents']))
            {
                foreach ($val['product_documents'] As $pdVal)
                {
                    if (!empty($pdVal['document']['doc_path']))
                    {
                        $prdDoc = $pdVal['document'];
                        $imgPath = __DIR__.'/../../../..'.$prdDoc['doc_path'];
                    
                        if(file_exists($imgPath))
                        {
                            if (!is_dir($_SERVER['DOCUMENT_ROOT'].'/'.'media/commerce/product/'))
                            {
                                mkdir($_SERVER['DOCUMENT_ROOT'].'/'.'media/commerce/product/', 0777);
                            }
                    
                            if (!is_dir($_SERVER['DOCUMENT_ROOT'].'/'.'media/commerce/product/'.$productId))
                            {
                                mkdir($_SERVER['DOCUMENT_ROOT'].'/'.'media/commerce/product/'.$productId, 0777);
                            }
                    
                            $newDocPath = 'media/commerce/product/'.$productId.'/'.basename($imgPath);
                    
                            copy($imgPath, $_SERVER['DOCUMENT_ROOT'].'/'.$newDocPath);
                            
                            $prdDoc['doc_path'] = '/'.$newDocPath;
                            $prdDoc['doc_type_id'] = $container['MelisDemoCommerceSetup']['docTypeIds'][$prdDoc['doc_type_id']];
                            $prdDoc['doc_subtype_id'] = $container['MelisDemoCommerceSetup']['docTypeIds'][$prdDoc['doc_subtype_id']];
                            $docId = $this->saveData($docTbl, $prdDoc);
                            
                            $prdDocRel = $pdVal['document_relation'];
                            $prdDocRel['rdoc_doc_id'] = $docId;
                            $prdDocRel['rdoc_product_id'] = $productId;
                            array_push($prdDocumentRelations, $prdDocRel);
                        }
                    }
                }
            }
            
            if (!empty($val['product_texts']))
            {
                foreach ($val['product_texts'] As $ptVal)
                {
                    $productText = $ptVal;
                    $productText['ptxt_prd_id'] = $productId;
                    $productText['ptxt_type'] = (!empty($container['MelisDemoCommerceSetup']['productTextTypeIds'][$productText['ptxt_type']])) ? $container['MelisDemoCommerceSetup']['productTextTypeIds'][$productText['ptxt_type']] : $productText['ptxt_type'];
                    array_push($prdTexts, $productText);
                }
            }
            
            if (!empty($val['product_prices']))
            {
                foreach ($val['product_prices'] As $ppVal)
                {
                    $productPrice = $ppVal;
                    $productPrice['price_prd_id'] = $productId;
                    array_push($prdPrices, $productPrice);
                }
            }
            
            if (!empty($val['site_config']))
            {
                $this->config = str_replace('\'[:'.$val['site_config'].']\'', $productId, $this->config);
            }
        }
        
//         $this->saveBatchData($prdCatTbl, $prdCategories);
        $this->saveBatchData($prdAttrTbl, $prdAttributes);
        $this->saveBatchData($docRelTbl, $prdDocumentRelations);
        $this->saveBatchData($prdTxtTbl, $prdTexts);
        $this->savePricesBatchData($priceTbl, $prdPrices);
        
        $container['MelisDemoCommerceSetup']['productIds'] = $this->productIds;
    }
    
    /**
     * Create entry for
     * @param array $categories
     */
    private function setupCategories($categories, $fatherId = -1)
    {
        $container = new Container('MelisDemoCommerceSetup');
    
        $catTbl = $this->getServiceManager()->get('MelisEcomCategoryTable');
        $catTransTbl = $this->getServiceManager()->get('MelisEcomCategoryTransTable');
        $catCountryTbl = $this->getServiceManager()->get('MelisEcomCountryCategoryTable');
        $seoTbl = $this->getServiceManager()->get('MelisEcomSeoTable');
        $docTbl = $this->getServiceManager()->get('MelisEcomDocumentTable');
        $docRelTbl = $this->getServiceManager()->get('MelisEcomDocRelationsTable');

        //get the melis.plugins.config.php
        // Getting the DemoSite config
        $melisSite = $_SERVER['DOCUMENT_ROOT'].'/../module/MelisSites';
        $modulePluginsConfigDir = $melisSite.'/MelisDemoCommerce/config/melis.plugins.config.php';
    
        $order = 1;
        foreach ($categories As $key => $val)
        {
            $catCols = $val['columns'];
            $catCols['cat_father_cat_id'] = $fatherId;
            $catCols['cat_order'] = $order++;
            $catCols['cat_date_creation'] = date('Y-m-d H:i:s');
            $catCols['cat_user_id_creation'] = 1;
    
            $catId = $this->saveData($catTbl, $catCols);
    
            foreach ($val['cat_translations'] As $ctVal)
            {
                $catTrans = $ctVal;
                $catTrans['catt_category_id'] = $catId;
                array_push($this->catTranslations, $catTrans);
                $this->categoryIds[$catTrans['catt_name']] = $catId;
            }
    
            $catCountry = $val['cat_country'];
            $catCountry['ccat_category_id'] = $catId;
            $this->saveData($catCountryTbl, $catCountry);
    
            if (!empty($val['cat_seo']))
            {
                foreach ($val['cat_seo'] As $sVal)
                {
                    $catSeo = $sVal;
                    $catSeo['eseo_page_id'] = (!empty($container['MelisDemoCommerceSetup']['pagesIds'][strtolower($catSeo['eseo_page_id']).'PageId'])) ? $container['MelisDemoCommerceSetup']['pagesIds'][strtolower($catSeo['eseo_page_id']).'PageId'] : $catSeo['eseo_page_id'];
                    $catSeo['eseo_category_id'] = $catId;
                    $this->saveData($seoTbl, $catSeo);
                }
            }
            
            if (!empty($val['cat_products']))
            {
                $catPrdOrder = 1;
                foreach ($val['cat_products'] As $cpVal)
                {
                    $catPrd = $cpVal;
                    $catPrd['pcat_cat_id'] = $catId;
                    $catPrd['pcat_prd_id'] = $container['MelisDemoCommerceSetup']['productIds'][$catPrd['pcat_prd_id']];
                    $catPrd['pcat_order'] = $catPrdOrder++;
                    array_push($this->catProducts, $catPrd);
                }
            }
    
            if (!empty($val['cat_documents']))
            {
                foreach ($val['cat_documents'] As $dKey => $dVal)
                {
                    if (!empty($dVal['document']['doc_path']))
                    {
                        $catDoc = $dVal['document'];
                        $imgPath = __DIR__.'/../../../..'.$catDoc['doc_path'];
    
                        if(file_exists($imgPath))
                        {
                            if (!is_dir($_SERVER['DOCUMENT_ROOT'].'/'.'media/commerce/category/'))
                            {
                                mkdir($_SERVER['DOCUMENT_ROOT'].'/'.'media/commerce/category/', 0777);
                            }
    
                            if (!is_dir($_SERVER['DOCUMENT_ROOT'].'/'.'media/commerce/category/'.$catId))
                            {
                                mkdir($_SERVER['DOCUMENT_ROOT'].'/'.'media/commerce/category/'.$catId, 0777);
                            }
    
                            $newDocPath = 'media/commerce/category/'.$catId.'/'.basename($imgPath);
    
                            copy($imgPath, $_SERVER['DOCUMENT_ROOT'].'/'.$newDocPath);
    
                            $catDoc['doc_path'] = '/'.$newDocPath;
                            $catDoc['doc_type_id'] = $container['MelisDemoCommerceSetup']['docTypeIds'][$catDoc['doc_type_id']];
                            $catDoc['doc_subtype_id'] = $container['MelisDemoCommerceSetup']['docTypeIds'][$catDoc['doc_subtype_id']];
                            $docId = $this->saveData($docTbl, $catDoc);
    
                            $catDocRel = $dVal['document_relation'];
                            $catDocRel['rdoc_doc_id'] = $docId;
                            $catDocRel['rdoc_category_id'] = $catId;
    
                            array_push($this->catDocumentRelations, $catDocRel);
                        }
                    }
                }
            }
    
            if (!empty($val['site_config']))
            {
                $this->config = str_replace('\'[:'.$val['site_config'].']\'', $catId, $this->config);
                //update plugins module config
                if (strpos(file_get_contents($modulePluginsConfigDir), '\'[:'.$val['site_config'].']\'') !== false) {
                    $modulePluginsConfig = preg_replace('(\'\[:'.$val['site_config'].'\]\')', $catId, file_get_contents($modulePluginsConfigDir));
                    file_put_contents($modulePluginsConfigDir, $modulePluginsConfig);
                }
            }
    
            if (!empty($val['cat_subCategories']))
            {
                $this->setupCategories($val['cat_subCategories'], $catId);
            }
        }
    }
    
    private function setupCategoriesIds()
    {
        $catTransTbl = $this->getServiceManager()->get('MelisEcomCategoryTransTable');
        $docRelTbl = $this->getServiceManager()->get('MelisEcomDocRelationsTable');
        $catPrdTbl = $this->getServiceManager()->get('MelisEcomProductCategoryTable');
        
        $this->saveBatchData($catTransTbl, $this->catTranslations);
        $this->saveBatchData($docRelTbl, $this->catDocumentRelations);
        $this->saveBatchData($catPrdTbl, $this->catProducts);
        $container = new Container('MelisDemoCommerceSetup');
        $container['MelisDemoCommerceSetup']['categoryIds'] = $this->categoryIds;
    }
    
    /**
     * Create entry for 
     * @param array $variants
     */
    private function setupVariants($variants)
    {
        $container = new Container('MelisDemoCommerceSetup');
        
        $varTbl = $this->getServiceManager()->get('MelisEcomVariantTable');
        $varAttrTbl = $this->getServiceManager()->get('MelisEcomProductVariantAttributeValueTable');
        $priceTbl = $this->getServiceManager()->get('MelisEcomPriceTable');
        $varStockTbl = $this->getServiceManager()->get('MelisEcomVariantStockTable');
        
        $varAttrs = array();
        $varPrices = array();
        $varStocks = array();
        foreach ($variants As $val)
        {
            $variant = $val['columns'];
            $variant['var_prd_id'] = (!empty($container['MelisDemoCommerceSetup']['productIds'][$variant['var_prd_id']])) ? $container['MelisDemoCommerceSetup']['productIds'][$variant['var_prd_id']] : $variant['var_prd_id'];
            $variant['var_user_id_creation'] = 1;
            $variant['var_date_creation'] = date('Y-m-d H:i:s');
            $variantId = $this->saveData($varTbl, $variant);
            
            $this->variantIds[$variant['var_sku']] = $variantId;
            if(!empty($val['variant_attributes']))
            {
                foreach ($val['variant_attributes'] As $vaVal)
                {
                    $varAttr = $vaVal;
                    $varAttr['vatv_variant_id'] = $variantId;
                    $varAttr['vatv_attribute_value_id'] = (!empty($container['MelisDemoCommerceSetup']['attrValuesIds'][$varAttr['vatv_attribute_value_id']])) ? $container['MelisDemoCommerceSetup']['attrValuesIds'][$varAttr['vatv_attribute_value_id']] : $varAttr['vatv_attribute_value_id'];
                    array_push($varAttrs, $varAttr);
                }
            }
            
            if (!empty($val['variant_prices']))
            {
                foreach ($val['variant_prices'] As $vpVal)
                {
                    $varPrice = $vpVal;
                    $varPrice['price_var_id'] = $variantId;
                    array_push($varPrices, $varPrice);
                }
            }
            
            if (!empty($val['variant_stocks']))
            {
                foreach ($val['variant_stocks'] As $vsVal)
                {
                    $varStock = $vsVal;
                    $varStock['stock_var_id'] = $variantId;
                    array_push($varStocks, $varStock);
                }
            }
        }
        
        $this->saveBatchData($varAttrTbl, $varAttrs);
        $this->savePricesBatchData($priceTbl, $varPrices);
        $this->saveStockBatchData($varStockTbl, $varStocks);
        
        $container['MelisDemoCommerceSetup']['variantIds'] = $this->variantIds;
    }

    private function setupCoupon($coupon)
    {
        $container = new Container('MelisDemoCommerceSetup');
        
        $couponTbl = $this->getServiceManager()->get('MelisEcomCouponTable');
        
        foreach ($coupon As $val)
        {
            $val['coup_percentage'] = ($val['coup_percentage']) ? $val['coup_percentage'] : null;
            $val['coup_discount_value'] = ($val['coup_discount_value']) ? $val['coup_discount_value'] : null;
            $val['coup_max_use_number'] = ($val['coup_max_use_number']) ? $val['coup_max_use_number'] : null;
            $val['coup_current_use_number'] = ($val['coup_current_use_number']) ? $val['coup_current_use_number'] : 0;
            $val['coup_date_valid_start'] = ($val['coup_date_valid_start']) ? $val['coup_date_valid_start'] : null;
            $val['coup_date_valid_end'] = ($val['coup_date_valid_end']) ? $val['coup_date_valid_end'] : null;
            $val['coup_date_creation'] = date('Y-m-d H:i:s');
            $val['coup_user_id'] = 1;
            $couponId = $this->saveData($couponTbl, $val);
            $this->couponIds[$val['coup_code']] = $couponId;
        }
        
        $container['MelisDemoCommerceSetup']['couponIds'] = $this->couponIds;
    }
    
    private function setupClientAndOrders($clientAndOrders)
    {
        $container = new Container('MelisDemoCommerceSetup');
        
        $clientTlb = $this->getServiceManager()->get('MelisEcomClientTable');
        $clientPersonTlb = $this->getServiceManager()->get('MelisEcomClientPersonTable');
        $clientAddressesTlb = $this->getServiceManager()->get('MelisEcomClientAddressTable');
        $orderTbl = $this->getServiceManager()->get('MelisEcomOrderTable');
        $orderBasketTbl = $this->getServiceManager()->get('MelisEcomOrderBasketTable');
        $orderAddTbl = $this->getServiceManager()->get('MelisEcomOrderAddressTable');
        $orderCouponTbl = $this->getServiceManager()->get('MelisEcomCouponOrderTable');
        $orderPaymentTbl = $this->getServiceManager()->get('MelisEcomOrderPaymentTable');
        $clientSrv = $this->getServiceManager()->get('MelisComClientService');
        $personRel = $this->getServiceManager()->get('MelisEcomClientPersonRelTable');
        $clientRel = $this->getServiceManager()->get('MelisEcomClientAccountRelTable');

        foreach ($clientAndOrders As $key => $val)
        {
            $client = $val['columns'];
            $client['cli_date_creation'] = date('Y-m-d H:i:s');
            $clientId = $this->saveData($clientTlb, $client);
            
            if (!empty($val['client_persons']))
            {
                foreach ($val['client_persons'] As $cpKey => $cpVal)
                {
                    $cperson = $cpVal['columns'];
                    $cperson['cper_client_id'] = $clientId;
                    $cperson['cper_password'] = $clientSrv->crypt($cperson['cper_password']);
                    $cperson['cper_date_creation'] = date('Y-m-d H:i:s');
                    $cperson['cper_date_edit'] = null;
                    
                    $personId = $this->saveData($clientPersonTlb, $cperson);

                    //insert client and contact association
                    if(!empty($clientId) && !empty($personId)){
                        //insert account rel
                        $this->saveData($clientRel, [
                            'car_client_id' => $clientId,
                            'car_client_person_id' => $personId,
                            'car_default_person' => 1
                        ]);
                        //insert contact rel
                        $this->saveData($personRel, [
                            'cpr_client_id' => $clientId,
                            'cpr_client_person_id' => $personId,
                            'cpr_default_client' => 1
                        ]);
                    }
                    
                    if (!empty($cpVal['client_person_addresses']))
                    {
                        foreach ($cpVal['client_person_addresses'] As $cpaVal)
                        {
                            $cpersonAdd = $cpaVal;
                            $cpersonAdd['cadd_client_id'] = $clientId;
                            $cpersonAdd['cadd_client_person'] = $personId;
                            $cpersonAdd['cadd_creation_date'] = date('Y-m-d H:i:s');
                            $this->saveData($clientAddressesTlb, $cpersonAdd);
                        }
                    }
                    
                    if (!empty($cpVal['client_person_orders']))
                    {
                        foreach ($cpVal['client_person_orders'] As $cpoVal)
                        {
                            $billingAddId = -1;
                            $deliveryAddId = -1;
                            
                            $order = $cpoVal['order'];
                            $order['ord_client_id'] = $clientId;
                            $order['ord_client_person_id'] = $personId;
                            $order['ord_billing_address'] = $billingAddId;
                            $order['ord_delivery_address'] = $deliveryAddId;
                            $order['ord_date_creation'] = date('Y-m-d H:i:s');
                            $orderId = $this->saveData($orderTbl, $order);
                            
                            foreach ($cpoVal['order_baskets'] As $cpobVal)
                            {
                                $orderBasket = $cpobVal;
                                $orderBasket['obas_order_id'] = $orderId;
                                $orderBasket['obas_variant_id'] = $container['MelisDemoCommerceSetup']['variantIds'][$orderBasket['obas_variant_id']];
                                $this->saveData($orderBasketTbl, $orderBasket);
                            }
                            
                            foreach ($cpoVal['order_addresses'] As $cpoaVal)
                            {
                                $orderAddress = $cpoaVal;
                                $orderAddress['oadd_order_id'] = $orderId;
                                $orderAddress['oadd_creation_date'] = date('Y-m-d H:i:s');
                                $oaddId = $this->saveData($orderAddTbl, $orderAddress);
                                if ($orderAddress['oadd_type'] == 1)
                                {
                                    $billingAddId = $oaddId;
                                }
                                else
                                {
                                    $deliveryAddId = $oaddId;
                                }
                            }
                            
                            $orderAdd = array(
                                'ord_billing_address' => $billingAddId,
                                'ord_delivery_address' => $deliveryAddId,
                            );
                            $orderTbl->save($orderAdd, $orderId);
                            
                            if (!empty($cpoVal['order_coupon']))
                            {
                                $orderCoupon = $cpoVal['order_coupon'];
                                $orderCoupon['cord_coupon_id'] = $container['MelisDemoCommerceSetup']['couponIds'][$orderCoupon['cord_coupon_id']];
                                $orderCoupon['cord_order_id'] = $orderId;
                                $this->saveData($orderCouponTbl, $orderCoupon);
                            }
                                
                            foreach ($cpoVal['order_payment'] As $cpopVal)
                            {
                                $orderPayment = $cpopVal;
                                $this->saveData($orderPaymentTbl, $orderPayment);
                            }
                        }
                    }
                }
            }
        }
    }
    
    private function saveData($tblModel, $data)
    {
        $tblModel->getTableGateway()->insert($data);
        $insertedId = $tblModel->getTableGateway()->lastInsertValue;
        return $insertedId;
    }
    
    private function saveStockBatchData($tblModel, $data)
    {
        $insertStr = 'INSERT INTO `'.$tblModel->getTableGateway()->getTable().'` (%s) VALUES %s';
        $insertValues = array();
        $query = '';
        $ctr = 1;
    
        $columns = array();
        $stockCols = array(
            'stock_var_id',
            'stock_country_id',
            'stock_quantity',
            'stock_next_fill_up',
        );
    
        $stockNullableCols = array(
            'stock_next_fill_up'
        );
        foreach ($stockCols As $val)
        {
            array_push($columns, '`'.$val.'`');
        }
    
        foreach ($data As $key => $val)
        {
            $values = array();
    
            foreach ($stockCols As $pcVal)
            {
                if (isset($val[$pcVal]))
                {
                    if (in_array($pcVal, $stockNullableCols))
                    {
                        if (empty($val[$pcVal]) && !is_numeric($val[$pcVal]))
                        {
                            array_push($values, 'NULL');
                        }
                        else
                        {
                            array_push($values, $val[$pcVal]);
                        }
                    }
                    else
                    {
                        array_push($values, (!is_numeric($val[$pcVal])) ? '"'.$val[$pcVal].'"' : $val[$pcVal]);
                    }
                }
                else
                {
                    array_push($values, 'NULL');
                }
            }
    
            array_push($insertValues, '('.implode(',', $values).')');
        }
    
        $query = sprintf($insertStr, implode(', ', $columns), implode(', ', $insertValues)).';';
    
        if (!empty($query))
        {
            $tblModel->getTableGateway()->getAdapter()->query($query, DbAdapter::QUERY_MODE_EXECUTE);
        }
    }
    
    private function saveAttributesBatchData($tblModel, $data)
    {
        $insertStr = 'INSERT INTO `'.$tblModel->getTableGateway()->getTable().'` (%s) VALUES %s';
        $insertValues = array();
        $query = '';
        $ctr = 1;
    
        $columns = array();
        $priceCols = array(
            'av_attribute_value_id',
            'avt_lang_id',
            'avt_v_int',
            'avt_v_float',
            'avt_v_bool',
            'avt_v_varchar',
            'avt_v_text',
            'avt_v_datetime',
            'avt_v_binary'
        );
    
        $pirceNullableCols = array(
            'avt_v_int',
            'avt_v_float',
            'avt_v_bool',
            'avt_v_datetime',
            'avt_v_binary',
        );
        foreach ($priceCols As $val)
        {
            array_push($columns, '`'.$val.'`');
        }
    
        foreach ($data As $key => $val)
        {
            $values = array();
    
            foreach ($priceCols As $pcVal)
            {
                if (isset($val[$pcVal]))
                {
                    if (in_array($pcVal, $pirceNullableCols))
                    {
                        if (empty($val[$pcVal]) && !is_numeric($val[$pcVal]))
                        {
                            array_push($values, 'NULL');
                        }
                        else
                        {
                            array_push($values, $val[$pcVal]);
                        }
                    }
                    else
                    {
                        array_push($values, (!is_numeric($val[$pcVal])) ? '"'.$val[$pcVal].'"' : $val[$pcVal]);
                    }
                }
                else
                {
                    array_push($values, 'NULL');
                }
            }
    
            array_push($insertValues, '('.implode(',', $values).')');
        }
    
        $query = sprintf($insertStr, implode(', ', $columns), implode(', ', $insertValues)).';';
    
        if (!empty($query))
        {
            $tblModel->getTableGateway()->getAdapter()->query($query, DbAdapter::QUERY_MODE_EXECUTE);
        }
    }
    
    private function savePricesBatchData($tblModel, $data)
    {
        $insertStr = 'INSERT INTO `'.$tblModel->getTableGateway()->getTable().'` (%s) VALUES %s';
        $insertValues = array();
        $query = '';
        $ctr = 1;
        
        $columns = array();
        $priceCols = array(
            'price_prd_id', 
            'price_var_id', 
            'price_country_id', 
            'price_currency', 
            'price_group_id', 
            'price_net', 
            'price_gross', 
            'price_vat_percent', 
            'price_vat_price', 
            'price_other_tax_price'
        );
        
        $pirceNullableCols = array(
            'price_prd_id',
            'price_var_id',
            'price_net',
            'price_gross',
            'price_vat_percent',
            'price_vat_price',
            'price_other_tax_price'
        );
        foreach ($priceCols As $val)
        {
            array_push($columns, '`'.$val.'`');
        }
        
        foreach ($data As $key => $val)
        {
            $values = array();
            
            foreach ($priceCols As $pcVal)
            {
                if (isset($val[$pcVal]))
                {
                    if (in_array($pcVal, $pirceNullableCols))
                    {
                        if (empty($val[$pcVal]) && !is_numeric($val[$pcVal]))
                        {
                            array_push($values, 'NULL');
                        }
                        else 
                        {
                            array_push($values, $val[$pcVal]);
                        }
                    }
                    else 
                    {
                        array_push($values, (!is_numeric($val[$pcVal])) ? '"'.$val[$pcVal].'"' : $val[$pcVal]);
                    }
                }
                else 
                {
                    array_push($values, 'NULL');
                }
            }
            
            array_push($insertValues, '('.implode(',', $values).')');
        }
        
        $query = sprintf($insertStr, implode(', ', $columns), implode(', ', $insertValues)).';';
        
        if (!empty($query))
        {
            $tblModel->getTableGateway()->getAdapter()->query($query, DbAdapter::QUERY_MODE_EXECUTE);
        }
    }
    
    private function saveBatchData($tblModel, $data)
    {
        $insertStr = 'INSERT INTO `'.$tblModel->getTableGateway()->getTable().'` (%s) VALUES %s';
        $insertValues = array();
        $query = '';
        $ctr = 1;
        foreach ($data As $key => $val)
        {
            $columns = array();
            $values = array();
            foreach ($val As $rkey => $rVal)
            {
                array_push($columns, '`'.$rkey.'`');
                array_push($values, (!is_numeric($rVal)) ? '"'.$rVal.'"' : $rVal);
            }
            
            array_push($insertValues, '('.implode(',', $values).')');
        }
        
        $query = sprintf($insertStr, implode(', ', $columns), implode(', ', $insertValues)).';';
        
        if (!empty($query))
        {
            try 
            {
                $tblModel->getTableGateway()->getAdapter()->query($query, DbAdapter::QUERY_MODE_EXECUTE);
            }
            catch (\Exception $e)
            {
                echo $e->getMessage().' : '.$tblModel->getTableGateway()->getTable();
            }
        }
    }
    
    private function saveRows($tblModel, $data) {
        
        $insertStr = 'INSERT INTO `'.$tblModel->getTableGateway()->getTable().'` (%s) VALUES (%s)';
        $query = '';
        $ctr = 1;
        foreach ($data As $key => $val)
        {
            $columns = array();
            $values = array();
            foreach ($val As $rkey => $rVal)
            {
                if (empty($columns))
                {
                    array_push($columns, '`'.$rkey.'`');
                }
                array_push($values, (!is_numeric($rVal)) ? '`'.$rVal.'`' : $rVal);
            }
            
            echo $ctr++.' : '.sprintf($insertStr, implode(', ', $columns), implode(', ', $values)).';<br>';
            
            $query .= sprintf($insertStr, implode(', ', $columns), implode(', ', $values)).';';
        }
        
        if (!empty($query))
        {
            $tblModel->getTableGateway()->getAdapter()->query($query, DbAdapter::QUERY_MODE_EXECUTE);
        }
    }
    
    public function testAction()
    {
                $container = new Container('MelisDemoCommerceSetup');
    
                echo '<pre>';
                print_r($container['MelisDemoCommerceSetup']);
                echo '</pre>';
    
        // Getting the DemoSite config
        //         $melisSite = $_SERVER['DOCUMENT_ROOT'].'/../module/MelisSites';
        //         $outputFileName = 'MelisDemoCommerce.config.php';
        //         $this->configDir = $melisSite.'/MelisDemoCommerce/config/'.$outputFileName;
    
    
        //         $this->config = file_get_contents($this->configDir);
    
        //         $this->config = str_replace('\'[:siteId]\'', 2, $this->config);
    
        //         echo '<pre>';
        //         print_r($this->config);
        //         echo '</pre>';
    
        //         $res = file_put_contents($this->configDir,  $this->config);
    
        //         if ($res)
            //             echo '1';
            //         else
                //             echo '2';
    
                //         return new JsonModel(array());
    }
}