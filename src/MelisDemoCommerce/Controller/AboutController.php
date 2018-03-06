<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2015 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisDemoCommerce\Controller;
use MelisDemoCommerce\Controller\BaseController;

class AboutController extends BaseController
{
    public function aboutusAction()
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
            'template_path' => 'MelisDemoCommerce/plugin/aboutus-slider',
            'id' => 'showSliderAboutUs',
            'sliderId' => $siteDatas['aboutus_slider'],
        );
        // add generated view to children views for displaying it in the contact view
        $this->view->addChild($showSlider->render($showSliderParameters), 'aboutUsSlider');
        
        $this->view->setVariable('idPage', $this->idPage);
        return $this->view;
        
    }
}