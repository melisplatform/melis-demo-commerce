<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2015 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisDemoCommerce\Controller;

use MelisFront\Service\MelisSiteConfigService;

class AboutController extends BaseController
{
    public function aboutusAction()
    {
        /** @var MelisSiteConfigService $siteConfigSrv */
        $siteConfigSrv = $this->getServiceManager()->get('MelisSiteConfigService');

        /**
         * Generating Homepage header Slider using MelisCmsSliderShowSliderPlugin Plugin
         */
        $showSlider = $this->MelisCmsSliderShowSliderPlugin();
        $showSliderParameters = array(
            'template_path' => 'MelisDemoCommerce/plugin/aboutus-slider',
            'id' => 'showSliderAboutUs',
            'sliderId' => $siteConfigSrv->getSiteConfigByKey('aboutus_slider', $this->idPage),
        );
        // add generated view to children views for displaying it in the contact view
        $this->view->addChild($showSlider->render($showSliderParameters), 'aboutUsSlider');
        
        $this->view->setVariable('idPage', $this->idPage);
        return $this->view;
        
    }
}