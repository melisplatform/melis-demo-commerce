<?php

namespace MelisDemoCommerce\Form\View\Helper;
 
use Laminas\Form\View\Helper\FormCollection;
use Laminas\Form\ElementInterface;
 
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