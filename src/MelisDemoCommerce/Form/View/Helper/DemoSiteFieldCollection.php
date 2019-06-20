<?php

namespace MelisDemoCommerce\Form\View\Helper;
 
use Zend\Form\View\Helper\FormCollection;
use Zend\Form\ElementInterface;
 
class DemoSiteFieldCollection extends FormCollection
{
    protected $defaultElementHelper = 'DemoSiteFieldRow';

    public function __invoke(ElementInterface $element = null, $wrap = false)
    {
        if (! $element) {
            return $this;
        }

        $this->setShouldWrap($wrap);

        return $this->render($element);
    }
}