<?php
/**
 * MagPassion_Productcarousel extension
 * 
 * @category   	MagPassion
 * @package		MagPassion_Productcarousel
 * @copyright  	Copyright (c) 2014 by MagPassion (http://magpassion.com)
 * @license	http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * Product Carousel edit form tab
 *
 * @category	MagPassion
 * @package		MagPassion_Productcarousel
 * @author MagPassion.com
 */
class MagPassion_Productcarousel_Block_Adminhtml_Productcarousel_Edit_Tab_Display extends Mage_Adminhtml_Block_Widget_Form{	
	
    /**
	 * prepare the form
	 * @access protected
	 * @return Productcarousel_Productcarousel_Block_Adminhtml_Productcarousel_Edit_Tab_Form
	 * @author MagPassion.com
	 */
	protected function _prepareForm(){
		$form = new Varien_Data_Form();
		$form->setHtmlIdPrefix('productcarousel_');
		$form->setFieldNameSuffix('productcarousel');
		$this->setForm($form);
		$fieldset = $form->addFieldset('productcarousel_form', array('legend'=>Mage::helper('productcarousel')->__('Display Options')));

		


		$fieldset->addField('showproductimage', 'select', array(
			'label' => Mage::helper('productcarousel')->__('Show product image'),
			'name'  => 'showproductimage',

			'values'=> array(
				array(
					'value' => '1',
					'label' => Mage::helper('productcarousel')->__('Yes'),
				),
				array(
					'value' => '0',
					'label' => Mage::helper('productcarousel')->__('No'),
				),
			),
            'value'=>'1',
		));
                $fieldset->addField('imagewidth', 'text', array(
			'label' => Mage::helper('productcarousel')->__('Image width'),
			'name'  => 'imagewidth',
			'required'  => true,
			'class' => 'required-entry',
            'class' => 'validate-number',
		));

		$fieldset->addField('imageheight', 'text', array(
			'label' => Mage::helper('productcarousel')->__('Image Height'),
			'name'  => 'imageheight',
			'required'  => true,
			'class' => 'required-entry',
            'class' => 'validate-number',
		));
        $fieldset->addField('newlabel', 'text', array(
			'label' => Mage::helper('productcarousel')->__('New product label'),
			'name'  => 'newlabel',
			'required'  => false,
                        'note' => $this->__('Set product new label. Example: New. Leave blank to disable.'),

	));
        $fieldset->addField('salelabel', 'text', array(
			'label' => Mage::helper('productcarousel')->__('Sale off product label'),
			'name'  => 'salelabel',
			'required'  => false,
                        'note' => $this->__('Set product sale label. Example: Sale. Leave blank to disable.'),

	));
        $fieldset->addField('showproductname', 'select', array(
			'label' => Mage::helper('productcarousel')->__('Show product name'),
			'name'  => 'showproductname',

			'values'=> array(
				array(
					'value' => '1',
					'label' => Mage::helper('productcarousel')->__('Yes'),
				),
				array(
					'value' => '0',
					'label' => Mage::helper('productcarousel')->__('No'),
				),
			),
            'value'=>'1',
		));
        
        
		$fieldset->addField('showmoredes', 'select', array(
			'label' => Mage::helper('productcarousel')->__('Show product short description'),
			'name'  => 'showmoredes',

			'values'=> array(
				array(
					'value' => '1',
					'label' => Mage::helper('productcarousel')->__('Yes'),
				),
				array(
					'value' => '0',
					'label' => Mage::helper('productcarousel')->__('No'),
				),
			),
            'value'=>'0',
            'note' => $this->__('Auto truncate 500 charaters.'),
		));

		$fieldset->addField('showproductprice', 'select', array(
			'label' => Mage::helper('productcarousel')->__('Show product price'),
			'name'  => 'showproductprice',

			'values'=> array(
				array(
					'value' => '1',
					'label' => Mage::helper('productcarousel')->__('Yes'),
				),
				array(
					'value' => '0',
					'label' => Mage::helper('productcarousel')->__('No'),
				),
			),
            'value'=>'1',
		));
                
                $fieldset->addField('showreview', 'select', array(
			'label' => Mage::helper('productcarousel')->__('Show product review summary'),
			'name'  => 'showreview',

			'values'=> array(
				array(
					'value' => '1',
					'label' => Mage::helper('productcarousel')->__('Yes'),
				),
				array(
					'value' => '0',
					'label' => Mage::helper('productcarousel')->__('No'),
				),
			),
            'value'=>'1',
		));

		$fieldset->addField('showproductaddtocart', 'select', array(
			'label' => Mage::helper('productcarousel')->__('Show add to cart button'),
			'name'  => 'showproductaddtocart',

			'values'=> array(
				array(
					'value' => '1',
					'label' => Mage::helper('productcarousel')->__('Yes'),
				),
				array(
					'value' => '0',
					'label' => Mage::helper('productcarousel')->__('No'),
				),
			),
            'value'=>'0',
		));


		$fieldset->addField('showmoreaddtolink', 'select', array(
			'label' => Mage::helper('productcarousel')->__('Show product add to link'),
			'name'  => 'showmoreaddtolink',

			'values'=> array(
				array(
					'value' => '1',
					'label' => Mage::helper('productcarousel')->__('Yes'),
				),
				array(
					'value' => '0',
					'label' => Mage::helper('productcarousel')->__('No'),
				),
			),
            'value'=>'1',
		));
        
        
        
        
        
       
        
        $fieldset->addField('showquickview', 'select', array(
			'label' => Mage::helper('productcarousel')->__('Show quickview product'),
			'name'  => 'showquickview',

			'values'=> array(
				array(
					'value' => '0',
					'label' => Mage::helper('productcarousel')->__('No'),
				),
				array(
					'value' => '1',
					'label' => Mage::helper('productcarousel')->__('Yes'),
				)
			),
            'value'=>'0',
            'note' => $this->__('Require MagPassion Quickview Extension installed.'),
		));
		
        if (Mage::getSingleton('adminhtml/session')->getProductcarouselData()){
			$form->setValues(Mage::getSingleton('adminhtml/session')->getProductcarouselData());
			Mage::getSingleton('adminhtml/session')->setProductcarouselData(null);
		}
		elseif (Mage::registry('current_productcarousel')){
			$form->setValues(Mage::registry('current_productcarousel')->getData());
		}
		return parent::_prepareForm();
	}
}