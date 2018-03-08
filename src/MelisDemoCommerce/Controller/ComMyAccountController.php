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
class ComMyAccountController extends BaseController
{
    public function indexAction()
    {
        /**
         * Getting the Login page id as Page redirected
         * if the user doesn't have authenticated yet
         */
        $siteConfig = $this->getServiceLocator()->get('config');
        $siteConfig = $siteConfig['site']['MelisDemoCommerce'];
        $siteDatas = $siteConfig['datas'];
        $loginPageId = $siteDatas['login_regestration_page_id'];
        // Generating the Redirect link using MelisEngineTree Service
        $melisTree = $this->getServiceLocator()->get('MelisEngineTree');
        $redirect_link = $melisTree->getPageLink($loginPageId, true);
        
        $countryId = $siteDatas['site_country_id'];
        
        /**
         * Checking if the user is logged in else
         * this will redirect to login page
         */
        $melisComAuthSrv = $this->getServiceLocator()->get('MelisComAuthenticationService');
        // only redirect if rendering is front
        if($this->renderMode == 'front'){
            if (!$melisComAuthSrv->hasIdentity())
            {
                $this->redirect()->toUrl($redirect_link);
            }
        }
        
        $clientAccount = $this->MelisCommerceAccountPlugin();
        $clientAccountParam = array(
            'template_path' => 'MelisDemoCommerce/plugin/account',
            'profile_parameter' => array(
                'template_path' => 'MelisDemoCommerce/plugin/profile'
            ),
            'delivery_address_parameter' => array(
                'template_path' => 'MelisDemoCommerce/plugin/delivery-address',
                'show_select_address_data' => true,
            ),
            'billing_address_parameter' => array(
                'template_path' => 'MelisDemoCommerce/plugin/billing-address',
                'show_select_address_data' => true,
            ),
            'cart_parameter' => array(
                'template_path' => 'MelisDemoCommerce/plugin/my-cart',
                'cart_country_id' => $countryId
            ),
            'order_list_paremeter' => array(
                'template_path' => 'MelisDemoCommerce/plugin/order-history',  
            ),
        );
        $this->view->addChild($clientAccount->render($clientAccountParam), 'account');
        
        $this->layout()->setVariables(array(
            'pageJs' => array(
                '/MelisDemoCommerce/js/melisSiteHelper.js',
                '/MelisDemoCommerce/js/account.js',
            ),
        ));
        
        $this->view->setVariable('idPage', $this->idPage);
        return $this->view;
    }
    
    public function saveProfileAction()
    {
        $status  = 0;
        $errors  = array();
        $personName = '';
         
        $request = $this->getRequest();
        
        if ($request->isPost())
        {
            $formType = $this->params()->fromQuery('form-type');
            
            $profilePlugin = $this->MelisCommerceProfilePlugin();
            $result = $profilePlugin->render(get_object_vars($request->getPost()))->getVariables();
            
            // Retrieving view variable from view
            $status = $result->success;
            $errors = $result->errors;
            
            if ($status)
            {
                $personName = $this->getUserName();
            }
        }
        
        $response = array(
            'personName' => $personName,
            'success' => $status,
            'errors' => $errors,
        );
         
        return new JsonModel($response);
    }

    public function getAddressAction()
    {
        $formData = array();
        $request = $this->getRequest();
        
        if($request->isPost())
        {
            $post = $request->getPost();
            
            // Concatinating post type address to create Plugin name
            $pluginClass = 'MelisCommerce'.ucfirst($post['type']).'AddressPlugin';
            
            $addressPlugin = $this->$pluginClass();
            $param = ArrayUtils::merge(get_object_vars($request->getPost()), array('show_select_address_data' => true));
            $result = $addressPlugin->render($param);
            
            $selAdd = $post['type'].'Address';
            
            $result->$selAdd->isvalid();
            $formData = $result->$selAdd->getData();
            $formData[$post['type'].'_address_save_submit'] = 1;
        }
        
        $response = array(
            'formData'  => $formData,
        );
        
        return new JsonModel($response);
    }

    public function submitAddressAction()
    {
        $status  = 0;
        $message  = '';
        $errors  = array();
        $selectAddresses  = array();
        $selectedId  = null;
        $formData = array();
        $showDeleteButton = false;
        
        $request = $this->getRequest();
        
        if($request->isPost()) {
            
            $post = $request->getPost();
            
            $type = ucfirst($post['type']);
            
            // Concatinating post type address to create Plugin name
            $pluginClass = 'MelisCommerce'.$type.'AddressPlugin';
            
            $addressPlugin = $this->$pluginClass();
            $param = ArrayUtils::merge(get_object_vars($request->getPost()), array('show_select_address_data' => true));
            $result = $addressPlugin->render($param)->getVariables();
            
            $selAdd = 'select'.$type.'Address';
            $selectAddresses = $result->$selAdd->get('select_'.$post['type'].'_addresses')->getValueOptions();
            $selectedId = $result->$selAdd->get('select_'.$post['type'].'_addresses')->getValue();
            
            if (is_numeric($selectedId))
            {
                $addForm = $post['type'].'Address';
                
                $result->$addForm->isvalid();
                $formData = $result->$addForm->getData();
                $formData[$post['type'].'_address_save_submit'] = 1;
            }
            $showDeleteButton = $result->showDeleteButton;
            $status = $result->success;
            $message = $result->message;
            $errors = $result->errors;
            
        }
        
        $response = array(
            'selectedId' => $selectedId,
            'selectAddresses' => $selectAddresses,
            'formData' => $formData,
            'showDeleteButton' => $showDeleteButton,
            'success' => $status,
            'message' => $message,
            'errors' => $errors,
        );
        
        return new JsonModel($response);
    }

    /**
     * Function to render the pagination of order history
     *
     * @return JsonModel
     */
    public function orderHistoryPaginationRendererAction()
    {
        $orderHistory = '';
        $request = $this->getRequest();

        if ($request->isPost())
        {
            // Getting the Site config "MelisDemoCommerce.config.php"
            $siteConfig = $this->getServiceLocator()->get('config');
            $siteConfig = $siteConfig['site']['MelisDemoCommerce'];
            $siteDatas = $siteConfig['datas'];

            $orderHistoryPlugin = $this->MelisCommerceOrderHistoryPlugin();
            $menuParameters = array(
                'template_path' => 'MelisDemoCommerce/plugin/order-history',
                'id' => 'orderHistoryPlugin',
            );
            $orderHistoryViewModel = $orderHistoryPlugin->render($menuParameters);

            // Rendering the viewmodel of the plugin
            $viewRender = $this->getServiceLocator()->get('ViewRenderer');
            $orderHistory = $viewRender->render($orderHistoryViewModel);
        }

        $response = array(
            'orderHistory' => $orderHistory,
        );

        return new JsonModel($response);
    }

    /**
     * Function to render the pagination of the cart
     *
     * @return JsonModel
     */
    public function cartPaginationRendererAction()
    {
        $cart = '';
        $request = $this->getRequest();

        if ($request->isPost())
        {
            // Getting the Site config "MelisDemoCommerce.config.php"
            $siteConfig = $this->getServiceLocator()->get('config');
            $siteConfig = $siteConfig['site']['MelisDemoCommerce'];
            $siteDatas = $siteConfig['datas'];

            $cartPlugin = $this->MelisCommerceCartPlugin();
            $menuParameters = array(
                'template_path' => 'MelisDemoCommerce/plugin/my-cart',
                'id' => 'cartPlugin',
            );
            $cartViewModel = $cartPlugin->render($menuParameters);

            // Rendering the viewmodel of the plugin
            $viewRender = $this->getServiceLocator()->get('ViewRenderer');
            $cart = $viewRender->render($cartViewModel);
        }

        $response = array(
            'cart' => $cart,
        );

        return new JsonModel($response);
    }
    
    private function getUserName()
    {
        $personName = null;
        $melisComAuthSrv = $this->getServiceLocator()->get('MelisComAuthenticationService');
        /**
         * Preparing the header link of the page
         * If user has been logged in the $personName will return
         * the name of the person logged in with frist name and last name
         */
        if ($melisComAuthSrv->hasIdentity())
        {
            $personName = $melisComAuthSrv->getPersonName();
        }
        
        return  $personName;
    }
}