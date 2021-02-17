<?php
namespace MelisDemoCommerce\Form\View\Helper;

use Laminas\Form\View\Helper\FormRow;
use Laminas\Form\ElementInterface;

class DemoSiteFieldRow extends FormRow
{
	const SELECT_FACTORY        = 'select';
	const CHECKBOX_FACTORY      = 'checkbox';

	public function render(ElementInterface $element, $labelPosition = null)
	{
		$formElement = '';
		$element->setLabelOption('class', 'control-label');
		
		if ($element->getAttribute('name') == 'm_variant_quantity')
		{
			$element->setAttribute('class', 'cart-plus-minus-box');
			$label = $element->getLabel();
			$element->setLabel('');
			$formElement .= '<div class="product-qty">
								<div class="cart-quantity">
									<div class="cart-plus-minus">
										<div class="dec qtybutton">-</div>
											'.parent::render($element, $labelPosition).'
										<div class="inc qtybutton">+</div>
									</div>
								</div>
							</div>';
		}
		elseif ($element->getAttribute('name') == 'm_om_message')
		{
			$element->setAttribute('class', 'area-tex');
			$label = $element->getLabel();
			$element->setLabel('');
			$formElement .= '<div class="col-md-12">
							<div class="input-box mb-20">
								'.parent::render($element, $labelPosition).'
							</div>
						</div>';	        
		}
		elseif ($element->getAttribute('name') == 'm_box_product_search')
		{
			$formElement .= '<div class="input-box"> 
								'.parent::render($element, $labelPosition).'
								<button id="catalogueSearch" class="src-btn sb-2"><i class="fa fa-search"></i></button>
							</div>';
		}
		elseif($element->getAttribute('type') == self::SELECT_FACTORY)
		{
			// render to bootstrap select element
			$element->setAttribute('class', '');
			$formElement .= '<div class="input-box mb-20">'. parent::render($element, $labelPosition).'</div>';
		}
		elseif ($element->getAttribute('type') == self::CHECKBOX_FACTORY)
		{
			// render to bootstrap select element
			$element->setAttribute('class', 'remr');
			$label = $element->getLabel();
			$element->setLabel('');
			$formElement .= '<span>'. parent::render($element, $labelPosition). $label .'<span>';
		}
		elseif ($element->getAttribute('type') != 'hidden') 
		{
			// render to bootstrap select element
			$element->setAttribute('class', 'info');
			$formElement .= '<div class="input-box mb-20">'. parent::render($element, $labelPosition).'</div>';
		}
		else 
		{
			$formElement .= parent::render($element, $labelPosition);
		}
		
		return $formElement;
	}
	
	/**
	 * Returns the class attribute of the element
	 * @param ElementInterface $element
	 * @return String
	 */
	protected function getClass(ElementInterface $element)
	{
		return $element->getAttribute('class');
	}
}