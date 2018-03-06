<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2015 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisDemoCommerce\Controller;

use MelisFront\Controller\MelisSiteActionController;
use Zend\View\Model\JsonModel;
use Zend\Session\Container;
// use Zend\Session\SessionManager;
// use Zend\Crypt\Key\Derivation\Pbkdf2;
// use Zend\Session\Container;


class TestController extends MelisSiteActionController
{
    public function testAction()
    {   
        $container = new Container('meliscommerce');
        
        echo '<pre>';
        print_r($container['checkout']);
//         $this->category();
//         $this->attributesTypes();
//         $this->attributes();
//         $this->textTypes();
//         $this->products();
//         $this->variants();
//         $this->clients();
//         $this->coupon();
        echo '</pre>';
        
        return new JsonModel(array());
    }
    
    public function category($fatherId = -1, $level = 0)
    {
        $catTbl = $this->getServiceLocator()->get('MelisEcomCategoryTable');
        $catCountryTbl = $this->getServiceLocator()->get('MelisEcomCountryCategoryTable');
        $catTransTbl = $this->getServiceLocator()->get('MelisEcomCategoryTransTable');
        $seoTbl = $this->getServiceLocator()->get('MelisEcomSeoTable');
        $docRelTbl = $this->getServiceLocator()->get('MelisEcomDocRelationsTable');
        $docTbl = $this->getServiceLocator()->get('MelisEcomDocumentTable');
        $docTypeTbl = $this->getServiceLocator()->get('MelisEcomDocTypeTable');
        $catPrdTbl = $this->getServiceLocator()->get('MelisEcomProductCategoryTable');
        $prdTbl = $this->getServiceLocator()->get('MelisEcomProductTable');
        $pageTreeSrv = $this->getServiceLocator()->get('MelisEnginePage');
        
        $lvl = str_repeat("\t", $level);
        foreach ($catTbl->getEntryByField('cat_father_cat_id', $fatherId)->toArray() As $val)
        {
            echo $lvl.'array('."\n";
            echo $lvl."\t".'\'columns\' => array('."\n";
            foreach ($val As $vKey => $vVal)
            {
                if (!in_array($vKey, array('cat_id', 'cat_father_cat_id', 'cat_date_valid_start', 'cat_date_valid_end', 'cat_order', 'cat_date_creation', 'cat_user_id_creation', 'cat_date_edit', 'cat_user_id_edit')))
                {
                    $tmpval = (is_numeric($vVal)) ? $vVal : '\''.$vVal.'\'';
                    echo $lvl."\t\t".'\''.$vKey.'\' => '.$tmpval.','."\n";
                }
            }
            echo $lvl."\t".'),'."\n";
            
            echo $lvl."\t".'\'cat_country\' => array('."\n";
            foreach ($catCountryTbl->getEntryByField('ccat_category_id', $val['cat_id'])->toArray() As $key => $sVal)
            {
                foreach ($sVal As $svKey => $svVal)
                {
                    if (!in_array($svKey, array('ccat_id', 'ccat_category_id')))
                    {
                        $tmpval = (is_numeric($svVal)) ? $svVal : '\''.$svVal.'\'';
                        echo $lvl."\t\t".'\''.$svKey.'\' => '.$tmpval.','."\n";
                    }
                }
            }
            echo $lvl."\t".'),'."\n";
            
            echo $lvl."\t".'\'cat_translations\' => array('."\n";
            foreach ($catTransTbl->getEntryByField('catt_category_id', $val['cat_id'])->toArray() As $sKey => $sVal)
            {
                echo $lvl."\t\t".'array('."\n";
                foreach ($sVal As $svKey => $svVal)
                {
                    if (!in_array($svKey, array('catt_id', 'catt_category_id')))
                    {
                        $tmpval = (!empty($svVal)) ? str_replace("'", "\'", $svVal) : $svVal;
                        $tmpval = (is_numeric($tmpval)) ? $tmpval : '\''.$tmpval.'\'';
                        
                        echo $lvl."\t\t\t".'\''.$svKey.'\' => '.$tmpval.','."\n";
                    }
                }
                
                $catName = lcfirst(str_replace(' ', '', ucwords(str_replace("'", "\'", $sVal['catt_name']))));
                
                echo $lvl."\t\t".'),'."\n";
            }
            echo $lvl."\t".'),'."\n";
            
            echo $lvl."\t".'\'site_config\' => \''.$catName.'CategoryId\','."\n";
            
            echo $lvl."\t".'\'cat_seo\' => array('."\n";
            foreach ($seoTbl->getEntryByField('eseo_category_id', $val['cat_id'])->toArray() As $sKey => $sVal)
            {
                echo $lvl."\t\t".'array('."\n";
                foreach ($sVal As $svKey => $svVal)
                {
                    if (!in_array($svKey, array('eseo_id', 'eseo_page_id', 'eseo_product_id', 'eseo_variant_id')))
                    {
                        $tmpval = (is_numeric($svVal)) ? $svVal : '\''.$svVal.'\'';
                        echo $lvl."\t\t\t".'\''.$svKey.'\' => '.$tmpval.','."\n";
                    }
                    elseif ($svKey == 'eseo_page_id')
                    {
                        $page = $pageTreeSrv->getDatasPage($svVal);
                        $prdRef = $page->getMelisPageTree()->page_name;
                        $tmpval = (is_numeric($prdRef)) ? $prdRef : '\''.$prdRef.'\'';
                        echo $lvl."\t\t\t".'\''.$svKey.'\' => '.$tmpval.','."\n";
                    }
                }
                echo $lvl."\t\t".'),'."\n";
            }
            echo $lvl."\t".'),'."\n";
            
            echo $lvl."\t".'\'cat_products\' => array('."\n";
            foreach ($catPrdTbl->getEntryByField('pcat_cat_id', $val['cat_id'])->toArray() As $sKey => $sVal)
            {
                echo $lvl."\t\t".'array('."\n";
                foreach ($sVal As $svKey => $svVal)
                {
                    if (!in_array($svKey, array('pcat_id', 'pcat_order', 'pcat_cat_id')))
                    {
                        $prdRef = $prdTbl->getEntryById($svVal)->current()->prd_reference;
                        $tmpval = (is_numeric($prdRef)) ? $prdRef : '\''.$prdRef.'\'';
                        echo $lvl."\t\t\t".'\''.$svKey.'\' => '.$tmpval.','."\n";
                    }
                }
                echo $lvl."\t\t".'),'."\n";
            }
            echo $lvl."\t".'),'."\n";
            
            echo $lvl."\t".'\'cat_documents\' => array('."\n";
            foreach ($docRelTbl->getEntryByField('rdoc_category_id', $val['cat_id'])->toArray() As $rdKey => $rdVal)
            {
                foreach ($docTbl->getEntryById($rdVal['rdoc_doc_id'])->toArray() As $dKey => $dVal)
                {
                    // Copying images from public media to Site pulic directory
                    /* if(is_dir(__DIR__.'\..\..\..\..\MelisDemoCommerce\public\images\products'))
                    {
                        mkdir(__DIR__.'\..\..\..\..\MelisDemoCommerce\public\images\products/'.$val['prd_reference'], 0777);
                        copy(HTTP_ROOT.$dVal['doc_path'],__DIR__.'\..\..\..\..\MelisDemoCommerce\public\images\products/'.$val['prd_reference'].'/'.$dVal['doc_name'].image_type_to_extension(getimagesize(HTTP_ROOT.$dVal['doc_path'])[2]));
                    } */
                    
                    echo $lvl."\t\t".'array('."\n";
                    echo $lvl."\t\t\t".'\'document\' =>array('."\n";
                    foreach ($dVal As $dvKey => $dvVal)
                    {
                        if (!in_array($dvKey, array('doc_id', 'doc_path', 'doc_type_id', 'doc_subtype_id')))
                        {
                            $tmpval = (is_numeric($dvVal)) ? $dvVal : '\''.$dvVal.'\'';
                            echo $lvl."\t\t\t\t".'\''.$dvKey.'\' => '.$tmpval.','."\n";
                        }
                        elseif (in_array($dvKey, array('doc_type_id', 'doc_subtype_id')))
                        {
                            echo $lvl."\t\t\t\t".'\''.$dvKey.'\' => \''.$docTypeTbl->getEntryById($dvVal)->current()->dtype_code.'\','."\n";
                        }
                        elseif ($dvKey == 'doc_path')
                        {
                            $fileName = $dVal['doc_name'].image_type_to_extension(getimagesize(HTTP_ROOT.$dVal['doc_path'])[2]);
                            echo $lvl."\t\t\t\t".'\''.$dvKey.'\' => \'/MelisDemoCommerce/public/images/categories/'.$fileName.'\','."\n";
                        }
                    }
                    echo $lvl."\t\t\t".'),'."\n";
                    echo $lvl."\t\t\t".'\'document_relation\' =>array('."\n";
                    echo $lvl."\t\t\t\t".'\'rdoc_country_id\' => -1,'."\n";
                    echo $lvl."\t\t\t".'),'."\n";
                    
                    echo $lvl."\t\t".'),'."\n";
                }
            }
            echo $lvl."\t".'),'."\n";
            
            echo $lvl."\t".'\'cat_subCategories\' => array('."\n";
                $this->category($val['cat_id'], $level + 2);
            echo $lvl."\t".'),'."\n";
            
            echo $lvl.'),'."\n";
        }
    }
    
    public function coupon()
    {
        $couponTbl = $this->getServiceLocator()->get('MelisEcomCouponTable');
        
        foreach ($couponTbl->fetchAll() As $val)
        {
            echo 'array('."\n";
                echo "\t".'\'columns\' => array('."\n";
                foreach ($val As $cKey => $vVal)
                {
                    if (!in_array($cKey, array('coup_id', 'coup_date_creation', 'coup_user_id')))
                    {
                        $tmpval = (is_numeric($vVal)) ? $vVal : '\''.$vVal.'\'';
                        echo "\t\t".'\''.$cKey.'\' => '.$tmpval.','."\n";
                    }
                }
                echo "\t".'),'."\n";
            echo '),'."\n";
        }
    }
    
    
    public function clients()
    {
        $clientTlb = $this->getServiceLocator()->get('MelisEcomClientTable');
        $clientPersonTlb = $this->getServiceLocator()->get('MelisEcomClientPersonTable');
        $clientAddressesTlb = $this->getServiceLocator()->get('MelisEcomClientAddressTable');
        $basketPersistentTlb = $this->getServiceLocator()->get('MelisEcomBasketPersistentTable');
        $orderTbl = $this->getServiceLocator()->get('MelisEcomOrderTable');
        $orderAddTbl = $this->getServiceLocator()->get('MelisEcomOrderAddressTable');
        $orderCouponTbl = $this->getServiceLocator()->get('MelisEcomCouponOrderTable');
        $couponTbl = $this->getServiceLocator()->get('MelisEcomCouponTable');
        $orderPaymentTbl = $this->getServiceLocator()->get('MelisEcomOrderPaymentTable');
        $orderShippingTbl = $this->getServiceLocator()->get('MelisEcomOrderShippingTable');
        $orderBasketTbl = $this->getServiceLocator()->get('MelisEcomOrderBasketTable');
        $varTbl = $this->getServiceLocator()->get('MelisEcomVariantTable');
        
        foreach ($clientTlb->fetchAll()->toArray() As $key => $val)
        {
            echo 'array('."\n";
                echo "\t".'\'columns\' => array('."\n";
                foreach ($val As $cKey => $vVal)
                {
                    if (!in_array($cKey, array('cli_id', 'cli_date_creation', 'cli_date_edit')))
                    {
                        $tmpval = (is_numeric($vVal)) ? $vVal : '\''.$vVal.'\'';
                        echo "\t\t".'\''.$cKey.'\' => '.$tmpval.','."\n";
                    }
                }
                echo "\t".'),'."\n";
                
                echo "\t".'\'client_persons\' => array('."\n";
                foreach ($clientPersonTlb->getEntryByField('cper_client_id', $val['cli_id'])->toArray() As $cKey => $vVal)
                {
                    foreach ($vVal As $cpvKey => $cpvVal)
                    {
                        if (!in_array($cpvKey, array('cper_id', 'cper_date_creation', 'cper_client_id')))
                        {
                            $tmpval = (is_numeric($cpvVal)) ? $cpvVal : '\''.$cpvVal.'\'';
                            echo "\t\t".'\''.$cpvKey.'\' => '.$tmpval.','."\n";
                        }
                    }
                    
                    echo "\t\t".'\'client_person_addresses\' => array('."\n";
                    foreach ($clientAddressesTlb->getEntryByField('cadd_client_person', $vVal['cper_id'])->toArray() As $cpsKey => $cpsVal)
                    {
                        echo "\t\t\t".'array('."\n";
                        foreach ($cpsVal As $cpsvKey => $cpsvVal)
                        {
                            if (!in_array($cpsvKey, array('cadd_id', 'cadd_client_id', 'cadd_client_person', 'cadd_creation_date')))
                            {
                                $tmpval = (is_numeric($cpsvVal)) ? $cpsvVal : '\''.$cpsvVal.'\'';
                                echo "\t\t\t\t".'\''.$cpsvKey.'\' => '.$tmpval.','."\n";
                            }
                        }
                        echo "\t\t\t".'),'."\n";
                    }
                    echo "\t\t".'),'."\n";
                    
                    echo "\t\t".'\'client_person_orders\' => array('."\n";
                    foreach ($orderTbl->getEntryByField('ord_client_id', $vVal['cper_id'])->toArray() As $cpsKey => $cpsVal)
                    {
                        echo "\t\t\t".'array('."\n";
                        echo "\t\t\t\t".'\'order\' => array('."\n";
                        foreach ($cpsVal As $cpsvKey => $cpsvVal)
                        {
                            if (!in_array($cpsvKey, array('ord_id', 'ord_client_id', 'ord_client_person_id', 'ord_date_creation', 'ord_billing_address', 'ord_delivery_address')))
                            {
                                $tmpval = (is_numeric($cpsvVal)) ? $cpsvVal : '\''.$cpsvVal.'\'';
                                echo "\t\t\t\t\t".'\''.$cpsvKey.'\' => '.$tmpval.','."\n";
                            }
                        }
                        echo "\t\t\t\t".'),'."\n";
                        
                        echo "\t\t\t\t".'\'order_baskets\' => array('."\n";
                        foreach ($orderBasketTbl->getEntryByField('obas_order_id', $cpsVal['ord_id'])->toArray() As $cpobKey => $cpobVal)
                        {
                            echo "\t\t\t\t\t".'array('."\n";
                            foreach ($cpobVal As $cpobvKey => $cpobvVal)
                            {
                                if (!in_array($cpobvKey, array('obas_id', 'obas_variant_id')))
                                {
                                    $tmpval = (is_numeric($cpobvVal)) ? $cpobvVal : '\''.$cpobvVal.'\'';
                                    echo "\t\t\t\t\t\t".'\''.$cpobvKey.'\' => '.$tmpval.','."\n";
                                }
                                elseif ($cpobvKey == 'obas_variant_id')
                                {
                                    $varSku = $varTbl->getEntryById($cpobvVal)->current()->var_sku;
                                    $tmpval = (is_numeric($varSku)) ? $varSku : '\''.$varSku.'\'';
                                    echo "\t\t\t\t\t\t".'\''.$cpobvKey.'\' => '.$tmpval.','."\n";
                                }
                            }
                            echo "\t\t\t\t\t".'),'."\n";
                        }
                        echo "\t\t\t\t".'),'."\n";
                        
                        echo "\t\t\t\t".'\'order_addresses\' => array('."\n";
                        foreach ($orderAddTbl->getEntryByField('oadd_order_id', $cpsVal['ord_id'])->toArray() As $cpavKey => $cpavVal)
                        {
                            echo "\t\t\t\t\t".'array('."\n";
                            foreach ($cpavVal As $cpavvKey => $cpavvVal)
                            {
                                if (!in_array($cpavvKey, array('oadd_id', 'oadd_order_id', 'oadd_creation_date')))
                                {
                                    $tmpval = (is_numeric($cpavvVal)) ? $cpavvVal : '\''.$cpavvVal.'\'';
                                    echo "\t\t\t\t\t\t".'\''.$cpavvKey.'\' => '.$tmpval.','."\n";
                                }
                            }
                            echo "\t\t\t\t\t".'),'."\n";
                        }
                        echo "\t\t\t\t".'),'."\n";
                        echo "\t\t\t\t".'\'order_coupon\' => array('."\n";
                        foreach ($orderCouponTbl->getEntryByField('cord_order_id', $cpsVal['ord_id'])->toArray() As $cpoKey => $cpoVal)
                        {
                            echo "\t\t\t\t\t".'array('."\n";
                            foreach ($cpoVal As $cpovKey => $cpovVal)
                            {
                                if ($cpovKey == 'cord_coupon_id')
                                {
                                    $coupon = $couponTbl->getEntryById($cpovVal)->current()->coup_code;
                                    $tmpval = (is_numeric($coupon)) ? $coupon : '\''.$coupon.'\'';
                                    echo "\t\t\t\t\t\t".'\''.$cpovKey.'\' => '.$tmpval.','."\n";
                                }
                            }
                            echo "\t\t\t\t\t".'),'."\n";
                        }
                        echo "\t\t\t\t".'),'."\n";
                        echo "\t\t\t\t".'\'order_payment\' => array('."\n";
                        foreach ($orderPaymentTbl->getEntryByField('opay_order_id', $cpsVal['ord_id'])->toArray() As $cpopKey => $cpopVal)
                        {
                            echo "\t\t\t\t\t".'array('."\n";
                            foreach ($cpopVal As $cpopvKey => $cpopvVal)
                            {
                                if (!in_array($cpavvKey, array('opay_id', 'opay_order_id')))
                                {
                                    $tmpval = (is_numeric($cpopvVal)) ? $cpopvVal : '\''.$cpopvVal.'\'';
                                    echo "\t\t\t\t\t\t".'\''.$cpopvKey.'\' => '.$tmpval.','."\n";
                                }
                            }
                            echo "\t\t\t\t\t".'),'."\n";
                        }
                        echo "\t\t\t\t".'),'."\n";
                        echo "\t\t\t\t".'\'order_shipping\' => array('."\n";
                        foreach ($orderShippingTbl->getEntryByField('oship_order_id', $cpsVal['ord_id'])->toArray() As $cposKey => $cposVal)
                        {
                            echo "\t\t\t\t\t".'array('."\n";
                            foreach ($cposVal As $cposvKey => $cposvVal)
                            {
                                if (!in_array($cpavvKey, array('oship_id', 'oship_order_id')))
                                {
                                    $tmpval = (is_numeric($cposvVal)) ? $cposvVal : '\''.$cposvVal.'\'';
                                    echo "\t\t\t\t\t\t".'\''.$cposvKey.'\' => '.$tmpval.','."\n";
                                }
                            }
                            echo "\t\t\t\t\t".'),'."\n";
                        }
                        echo "\t\t\t\t".'),'."\n";
                        echo "\t\t\t".'),'."\n";
                    }
                    echo "\t\t".'),'."\n";
                    
                }
                echo "\t".'),'."\n";
                
                
            echo '),'."\n";
        }
    }
    
    public function variants()
    {
        $varTbl = $this->getServiceLocator()->get('MelisEcomVariantTable');
        $prdTbl = $this->getServiceLocator()->get('MelisEcomProductTable');
        $varAttrTbl = $this->getServiceLocator()->get('MelisEcomProductVariantAttributeValueTable');
        $attrValTable = $this->getServiceLocator()->get('MelisEcomAttributeValueTable');
        $attrTypeTable = $this->getServiceLocator()->get('MelisEcomAttributeTypeTable');
        $attrValTransTable = $this->getServiceLocator()->get('MelisEcomAttributeValueTransTable');
        $attrTable = $this->getServiceLocator()->get('MelisEcomAttributeTable');
        $priceTbl = $this->getServiceLocator()->get('MelisEcomPriceTable');
        $varStockTbl = $this->getServiceLocator()->get('MelisEcomVariantStockTable');
        
        foreach ($varTbl->fetchAll()->toArray() As $key => $val)
        {
            echo 'array('."\n";
                echo "\t".'\'columns\' => array('."\n";
                foreach ($val As $vKey => $vVal)
                {
                    if (!in_array($vKey, array('var_id', 'var_prd_id', 'var_date_creation', 'var_user_id_creation', 'var_date_edit', 'var_user_id_edit')))
                    {
                        $tmpval = (is_numeric($vVal)) ? $vVal : '\''.$vVal.'\'';
                        echo "\t\t".'\''.$vKey.'\' => '.$tmpval.','."\n";
                    }
                    elseif ($vKey == 'var_prd_id')
                    {
                        $tmpval = $prdTbl->getEntryById($vVal)->current()->prd_reference;
                        $tmpval = (is_numeric($tmpval)) ? $tmpval : '\''.$tmpval.'\'';
                        echo "\t\t".'\''.$vKey.'\' => '.$tmpval.','."\n";
                    }
                }
                echo "\t".'),'."\n";
                
                echo "\t".'\'variant_attributes\' => array('."\n";
                foreach ($varAttrTbl->getEntryByField('vatv_variant_id', $val['var_id'])->toArray() As $vaKey => $vaVal)
                {
                    
                    echo "\t\t".'array('."\n";
                    foreach ($vaVal As $vavKey => $vavVal)
                    {
                        if ($vavKey == 'vatv_attribute_value_id')
                        {
                            $attrVal = $attrValTable->getEntryById($vavVal)->current();
                            
                            $attrType = 'avt_v_'.$attrTypeTable->getEntryById($attrVal->atval_type_id)->current()->atype_column_value;
                            
                            $attrRef = $attrTable->getEntryByid($attrVal->atval_attribute_id)->current()->attr_reference;
                            $attrValTrans = $attrValTransTable->getEntryByField('av_attribute_value_id', $attrVal->atval_id)->current()->$attrType;
                            
                            $tmpval = (is_numeric($vavVal)) ? $vavVal : '\''.$vavVal.'\'';
                            echo "\t\t\t".'\''.$vavKey.'\' => \''.$attrRef.'_'.$attrValTrans.'\','."\n";
                        }
                    }
                    echo "\t\t".'),'."\n";
                }
                echo "\t".'),'."\n";
                
                echo "\t".'\'variant_prices\' => array('."\n";
                foreach ($priceTbl->getEntryByField('price_var_id', $val['var_id'])->toArray() As $ppKey => $ppVal)
                {
                    echo "\t\t".'array('."\n";
                    foreach ($ppVal As $ppvKey => $ppvVal)
                    {
                        if (!in_array($ppvKey, array('price_id', 'price_var_id')))
                        {
                            $tmpval = (is_numeric($ppvVal)) ? $ppvVal : '\''.$ppvVal.'\'';
                            echo "\t\t\t".'\''.$ppvKey.'\' => '.$tmpval.','."\n";
                        }
                    }
                    echo "\t\t".'),'."\n";
                }
                echo "\t".'),'."\n";
                
                echo "\t".'\'variant_stocks\' => array('."\n";
                foreach ($varStockTbl->getEntryByField('stock_var_id', $val['var_id'])->toArray() As $vsKey => $vsVal)
                {
                    echo "\t\t".'array('."\n";
                    foreach ($vsVal As $vsvKey => $vsvVal)
                    {
                        if (!in_array($vsvKey, array('stock_id')))
                        {
                            $tmpval = (is_numeric($vsvVal)) ? $vsvVal : '\''.$vsvVal.'\'';
                            echo "\t\t\t".'\''.$vsvKey.'\' => '.$tmpval.','."\n";
                        }
                    }
                    echo "\t\t".'),'."\n";
                }
                echo "\t".'),'."\n";
                
            echo '),'."\n";
        }
    }
    
    public function textTypes()
    {
        $prdTextTypesTbl = $this->getServiceLocator()->get('MelisEcomProductTextTypeTable');
        
        foreach ($prdTextTypesTbl->fetchAll()->toArray() As $key => $val)
        {
            echo 'array('."\n";
            foreach ($val As $vKey => $vVal)
            {
                $tmpval = (is_numeric($vVal)) ? $vVal : '\''.$vVal.'\'';
                echo "\t".'\''.$vKey.'\' => '.$tmpval.','."\n";
            }
            echo '),'."\n";
        }
    }
    
    public function attributesTypes()
    {
        $attrTypeTable = $this->getServiceLocator()->get('MelisEcomAttributeTypeTable');
        
        foreach ($attrTypeTable->fetchAll()->toArray() As $key => $val)
        {
            echo 'array('."\n";
            echo "\t".'\'columns\' => array('."\n";
            foreach ($val As $vKey => $vVal)
            {
                if (!in_array($vKey, array('atype_id')))
                {
                    $tmpval = (is_numeric($vVal)) ? $vVal : '\''.$vVal.'\'';
                    echo "\t\t".'\''.$vKey.'\' => '.$tmpval.','."\n";
                }
            }
            echo "\t".'),'."\n";
            echo '),'."\n";
        }
    }
    
    public function attributes()
    {
        $attrTable = $this->getServiceLocator()->get('MelisEcomAttributeTable');
        $attrTransTable = $this->getServiceLocator()->get('MelisEcomAttributeTransTable');
        $attrTypeTable = $this->getServiceLocator()->get('MelisEcomAttributeTypeTable');
        $attrValTable = $this->getServiceLocator()->get('MelisEcomAttributeValueTable');
        $attrValTransTable = $this->getServiceLocator()->get('MelisEcomAttributeValueTransTable');
        
        foreach ($attrTable->fetchAll()->toArray() As $key => $val)
        {
            echo 'array('."\n";
            echo "\t".'\'columns\' => array('."\n";
            foreach ($val As $vKey => $vVal)
            {
                if (!in_array($vKey, array('attr_id', 'attr_type_id')))
                {
                    $tmpval = (is_numeric($vVal)) ? $vVal : '\''.$vVal.'\'';
                    echo "\t\t".'\''.$vKey.'\' => '.$tmpval.','."\n";
                }
                elseif ($vKey == 'attr_type_id')
                {
                    $attrType = $attrTypeTable->getEntryById($vVal)->current()->atype_name;
                    $tmpval = (is_numeric($attrType)) ? $attrType : '\''.$attrType.'\'';
                    echo "\t\t".'\''.$vKey.'\' => '.$tmpval.','."\n";
                }
            }
            echo "\t".'),'."\n";
            
            echo "\t".'\'attribute_trans\' => array('."\n";
            foreach ($attrTransTable->getEntryByField('atrans_attribute_id', $val['attr_id'])->toArray() As $atKey => $atVal)
            {
                echo "\t\t".'array('."\n";
                foreach ($atVal As $atvKey => $atvVal)
                {
                    if (!in_array($atvKey, array('atrans_id', 'atrans_attribute_id')))
                    {
                        $tmpval = (is_numeric($atvVal)) ? $atvVal : '\''.$atvVal.'\'';
                        echo "\t\t\t".'\''.$atvKey.'\' => '.$tmpval.','."\n";
                    }
                }
                echo "\t\t".'),'."\n";
            }
            echo "\t".'),'."\n";
            
            echo "\t".'\'attribute_values\' => array('."\n";
            foreach ($attrValTable->getEntryByField('atval_attribute_id', $val['attr_id'])->toArray() As $atKey => $atVal)
            {
                echo "\t\t".'array('."\n";
                echo "\t\t\t".'\'columns\' => array('."\n";
                foreach ($atVal As $atvKey => $atvVal)
                {
                    if (!in_array($atvKey, array('atval_id', 'atval_attribute_id', 'atval_type_id')))
                    {
                        $tmpval = (is_numeric($atvVal)) ? $atvVal : '\''.$atvVal.'\'';
                        echo "\t\t\t\t".'\''.$atvKey.'\' => '.$tmpval.','."\n";
                    }
                    elseif ($atvKey == 'atval_type_id')
                    {
                        $attrType = $attrTypeTable->getEntryById($atvVal)->current()->atype_name;
                        $tmpval = (is_numeric($attrType)) ? $attrType : '\''.$attrType.'\'';
                        echo "\t\t\t\t".'\''.$atvKey.'\' => '.$tmpval.','."\n";
                    }
                }
                echo "\t\t\t".'),'."\n";
                
                echo "\t\t\t".'\'attribute_value_trans\' => array('."\n";
                foreach ($attrValTransTable->getEntryByField('av_attribute_value_id', $atVal['atval_id'])->toArray() As $avtKey => $avtVal)
                {
                    echo "\t\t\t\t".'array('."\n";
                    foreach ($avtVal As $avtvKey => $avtvVal)
                    {
                        if (!in_array($avtvKey, array('avt_id', 'av_attribute_value_id')))
                        {
                            $tmpval = (is_numeric($avtvVal)) ? $avtvVal : '\''.$avtvVal.'\'';
                            echo "\t\t\t\t\t".'\''.$avtvKey.'\' => '.$tmpval.','."\n";
                        }
                    }
                    echo "\t\t\t\t".'),'."\n";
                }
                echo "\t\t\t".'),'."\n";
                
                echo "\t\t".'),'."\n";
            }
            echo "\t".'),'."\n";
            
            echo '),'."\n";
        }
    }
    
    public function products()
    {
        $prdTbl = $this->getServiceLocator()->get('MelisEcomProductTable');
        $prdCatTbl = $this->getServiceLocator()->get('MelisEcomProductCategoryTable');
        $catSrv = $this->getServiceLocator()->get('MelisComCategoryService');
        $prdAttrTbl = $this->getServiceLocator()->get('MelisEcomProductAttributeTable');
        $attrTable = $this->getServiceLocator()->get('MelisEcomAttributeTable');
        $docRelTbl = $this->getServiceLocator()->get('MelisEcomDocRelationsTable');
        $docTbl = $this->getServiceLocator()->get('MelisEcomDocumentTable');
        $docTypeTbl = $this->getServiceLocator()->get('MelisEcomDocTypeTable');
        $prdTxtTbl = $this->getServiceLocator()->get('MelisEcomProductTextTable');
        $prdTxtTypeTbl = $this->getServiceLocator()->get('MelisEcomProductTextTypeTable');
        $priceTbl = $this->getServiceLocator()->get('MelisEcomPriceTable');
        $catTbl = $this->getServiceLocator()->get('MelisEcomCategoryTable');
        
        foreach ($prdTbl->fetchAll()->toArray() As $key => $val)
        {
            echo 'array('."\n";
                echo "\t".'\'columns\' => array('."\n";
                foreach ($val As $vKey => $vVal)
                {
                    if (!in_array($vKey, array('prd_id', 'prd_date_creation', 'prd_user_id_creation', 'prd_date_edit', 'prd_user_id_edit')))
                    {
                        $tmpval = (is_numeric($vVal)) ? $vVal : '\''.$vVal.'\'';
                        echo "\t\t".'\''.$vKey.'\' => '.$tmpval.','."\n";
                    }
                }
                echo "\t".'),'."\n";
                
//                 echo "\t".'\'product_categories\' => array('."\n";
//                 foreach ($prdCatTbl->getEntryByField('pcat_prd_id', $val['prd_id'])->toArray() As $pcKey => $pcVal)
//                 {
//                     $cat = $catTbl->getEntryById($pcVal['pcat_cat_id'])->current();
                    
//                     echo "\t\t".'array('."\n";
//                         echo "\t\t\t".'\'pcat_cat_id\' => \''.str_replace("'", "\'", $cat->cat_father_cat_id.' '.$catSrv->getCategoryNameById($pcVal['pcat_cat_id'], 1)).'\''."\n";
//                     echo "\t\t".'),'."\n";
//                 }
//                 echo "\t".'),'."\n";
                
                echo "\t".'\'product_attributes\' => array('."\n";
                foreach ($prdAttrTbl->getEntryByfield('patt_product_id', $val['prd_id'])->toArray() As $paKey => $paval)
                {
                    echo "\t\t".'array('."\n";
                    echo "\t\t\t".'\'patt_attribute_id\' => \''.str_replace("'", "\'", $attrTable->getEntryById($paval['patt_attribute_id'])->current()->attr_reference).'\''."\n";
                    echo "\t\t".'),'."\n";
                }
                echo "\t".'),'."\n";
                
                echo "\t".'\'product_documents\' => array('."\n";
                foreach ($docRelTbl->getEntryByField('rdoc_product_id', $val['prd_id'])->toArray() As $rdKey => $rdVal)
                {
                    foreach ($docTbl->getEntryById($rdVal['rdoc_doc_id'])->toArray() As $dKey => $dVal)
                    {
                        // Copying images from public media to Site pulic directory
                        /* if(is_dir(__DIR__.'\..\..\..\..\MelisDemoCommerce\public\images\products'))
                        {
                            mkdir(__DIR__.'\..\..\..\..\MelisDemoCommerce\public\images\products/'.$val['prd_reference'], 0777);
                            copy(HTTP_ROOT.$dVal['doc_path'],__DIR__.'\..\..\..\..\MelisDemoCommerce\public\images\products/'.$val['prd_reference'].'/'.$dVal['doc_name'].image_type_to_extension(getimagesize(HTTP_ROOT.$dVal['doc_path'])[2]));
                        } */
                        
                        echo "\t\t".'array('."\n";
                        echo "\t\t\t".'\'document\' =>array('."\n";
                        foreach ($dVal As $dvKey => $dvVal)
                        {
                            if (!in_array($dvKey, array('doc_id', 'doc_path', 'doc_type_id', 'doc_subtype_id')))
                            {
                                $tmpval = (is_numeric($dvVal)) ? $dvVal : '\''.$dvVal.'\'';
                                echo "\t\t\t\t".'\''.$dvKey.'\' => '.$tmpval.','."\n";
                            }
                            elseif (in_array($dvKey, array('doc_type_id', 'doc_subtype_id')))
                            {
                                echo "\t\t\t\t".'\''.$dvKey.'\' => \''.$docTypeTbl->getEntryById($dvVal)->current()->dtype_code.'\','."\n";
                            }
                            elseif ($dvKey == 'doc_path')
                            {
                                $fileName = $dVal['doc_name'].image_type_to_extension(getimagesize(HTTP_ROOT.$dVal['doc_path'])[2]);
                                echo "\t\t\t\t".'\''.$dvKey.'\' => \'/MelisDemoCommerce/public/images/products/'.$val['prd_reference'].'/'.$fileName.'\','."\n";
                            }
                        }
                        echo "\t\t\t".'),'."\n";
                        echo "\t\t\t".'\'document_relation\' =>array('."\n";
                        echo "\t\t\t\t".'\'rdoc_country_id\' => -1,'."\n";
                        echo "\t\t\t".'),'."\n";
                        
                        
                        echo "\t\t".'),'."\n";
                    }
                }
                echo "\t".'),'."\n";
                
                echo "\t".'\'product_texts\' => array('."\n";
                foreach ($prdTxtTbl->getEntryByField('ptxt_prd_id', $val['prd_id'])->toArray() As $ptKey => $ptVal)
                {
                    echo "\t\t".'array('."\n";
                    foreach ($ptVal As $ptvKey => $ptvVal)
                    {
                        if (!in_array($ptvKey, array('ptxt_id', 'ptxt_prd_id', 'ptxt_type')))
                        {
                            $tmpval = (is_numeric($ptvVal)) ? $ptvVal : '\''.$ptvVal.'\'';
                            echo "\t\t\t".'\''.$ptvKey.'\' => '.$tmpval.','."\n";
                        }
                        elseif ($ptvKey == 'ptxt_type')
                        {
                            $textType = $prdTxtTypeTbl->getEntryById($ptvVal)->current()->ptt_code;
                            $tmpval = (is_numeric($textType)) ? $textType : '\''.$textType.'\'';
                            echo "\t\t\t".'\''.$ptvKey.'\' => '.$tmpval.','."\n";
                        }
                    }
                    echo "\t\t".'),'."\n";
                }
                echo "\t".'),'."\n";
                
                echo "\t".'\'product_prices\' => array('."\n";
                foreach ($priceTbl->getEntryByField('price_prd_id', $val['prd_id'])->toArray() As $ppKey => $ppVal)
                {
                    echo "\t\t".'array('."\n";
                    foreach ($ppVal As $ppvKey => $ppvVal)
                    {
                        if (!in_array($ppvKey, array('price_id', 'price_prd_id')))
                        {
                            $tmpval = (is_numeric($ppvVal)) ? $ppvVal : '\''.$ppvVal.'\'';
                            echo "\t\t\t".'\''.$ppvKey.'\' => '.$tmpval.','."\n";
                            
                        }
                    }
                    echo "\t\t".'),'."\n";
                }
                echo "\t".'),'."\n";
                
                
                
            echo '),'."\n";
        }
    }
    
    public function categorySliderListProductsPluginViewAction()
    {
        /**
         * Generating new arrivals category list
         */
        $categorySliderListProductsPluginView = $this->MelisCommerceCategorySliderListProductsPlugin();
        $categorySliderListProductsParameters = array(
            'template_path' => 'MelisDemoCommerce/plugins/new-arrivals-slider',
            'categoriesId' => array(20,21,22), // the categories ID,
        );
         
        $pluginView =  $categorySliderListProductsPluginView->render($categorySliderListProductsParameters)->getVariables();
        echo '<pre>';
        print_r($pluginView->categorySliderListProducts);
        echo '</pre>';
        die();
        
//         $this->view->addChild($categorySliderListProductsPluginView->render($categorySliderListProductsParameters), 'newArrivalsListSlider');
    }
    
    public function filterMenuProductSearchBoxPluginViewAction()
    {
        /**
         * Generating new arrivals category list
         */
        $filterMenuProductSearchBoxPluginView = $this->MelisCommerceFilterMenuProductSearchBoxPlugin();
        $filterMenuProductSearchBoxParemeters = array(
            'template_path' => 'MelisDemoCommerce/plugins/new-arrivals-slider',  
        );
        
        $pluginView = $filterMenuProductSearchBoxPluginView->render($filterMenuProductSearchBoxParemeters)->getVariables();
        echo '<pre>';
        print_r($pluginView->categorySliderListProducts);
        echo '</pre>';
        die();
    }
    
    public function filterMenuCategoryListPluginViewAction()
    {
        /**
         * Generating new arrivals category list
         */
        $filterMenuCategoryListPluginView = $this->MelisCommerceFilterMenuCategoryListPlugin();
        $filterMenuCategoryListParameters = array(
            'template_path' => 'MelisDemoCommerce/plugins/new-arrivals-slider',
            'parent_category_id' => 1,
        );
        
        $pluginView = $filterMenuCategoryListPluginView->render($filterMenuCategoryListParameters)->getVariables();
        echo '<pre>';
        print_r($pluginView->filterMenuCategoryList);
        echo '</pre>';
        die();
    }
    
    public function filterMenuPriceValueBoxPluginViewAction()
    {
        /**
         * Generating new arrivals category list
         */
        $filterMenuPriceValueBoxPluginView = $this->MelisCommerceFilterMenuPriceValueBoxPlugin();
        $filterMenuPriceValueBoxParameters = array(
            'template_path' => 'MelisDemoCommerce/plugins/new-arrivals-slider',
            'm_box_filter_price_min' => 1,
            'm_box_filter_price_max' => 50000,
        );
        
        $pluginView = $filterMenuPriceValueBoxPluginView->render($filterMenuPriceValueBoxParameters)->getVariables();
        echo '<pre>';
        print_r($pluginView->filterMenuPriceValue);
        echo '</pre>';
        die();
    }
    
    public function filterMenuAttributeValueBoxPluginViewAction()
    {
        /**
         * Generating new arrivals category list
         */
        $filterMenuAttributeValueBoxPluginView = $this->MelisCommerceFilterMenuAttributeValueBoxPlugin();
        $filterMenuAttributeValueBoxParameters = array(
            'template_path' => 'MelisDemoCommerce/plugins/new-arrivals-slider',
            'attribute_id' => 5,
        );
    
        $pluginView = $filterMenuAttributeValueBoxPluginView->render($filterMenuAttributeValueBoxParameters)->getVariables();
        echo '<pre>';
        print_r($pluginView->filterMenuAttributeValue);
        echo '</pre>';
        die();
    }
    
    public function productShowPluginViewAction()
    {
        /**
         * Generating new product show product
         */        
        $productShowPluginView = $this->MelisCommerceProductShowPlugin();
        $productShowParemeters = array(
//             'template_path' => 'MelisDemoCommerce/plugins/new-arrivals-slider',
            'm_p_id' => 1,
            'm_p_timage' => 3,
            'm_p_timage_ok' => array(1),
        ); 
        
        $pluginView = $productShowPluginView->render($productShowParemeters)->getVariables();
        echo '<pre>';
        print_r($pluginView->product);
        print_r($pluginView->product_images);
        echo '</pre>';
        die();
    }
    
    public function productsRelatedPluginViewAction()
    {
        /**
         * Generating new product show product
         */
        $productsRelatedPluginView = $this->MelisCommerceProductsRelatedPlugin(); 
        $productsRelatedParameters = array(
            'template_path' => 'MelisDemoCommerce/plugins/new-arrivals-slider',
            'm_p_id' => 1,
        );
        
        $pluginView = $productsRelatedPluginView->render($productsRelatedParameters)->getVariables();
        echo '<pre>';
        print_r($pluginView->associatedVariants);
        echo '</pre>';
        die();
    }
}