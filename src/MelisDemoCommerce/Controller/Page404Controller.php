<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2015 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisDemoCommerce\Controller;


class Page404Controller extends BaseController
{
    public function indexAction()
    {
        $this->view->setVariable('idPage', $this->idPage);
        $this->view->setVariable('renderType', $this->renderType);
        $this->view->setVariable('renderMode', $this->renderMode);
        return $this->view;
    }
}