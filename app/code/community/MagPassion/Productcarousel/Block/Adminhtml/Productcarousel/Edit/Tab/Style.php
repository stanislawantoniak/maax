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
class MagPassion_Productcarousel_Block_Adminhtml_Productcarousel_Edit_Tab_Style extends Mage_Adminhtml_Block_Widget_Form{	
	
    /**
	 * prepare the form
	 * @access protected
	 * @return Productcarousel_Productcarousel_Block_Adminhtml_Productcarousel_Edit_Tab_Style
	 * @author MagPassion.com
	 */
	protected function _prepareForm(){
		$form = new Varien_Data_Form();
		$form->setHtmlIdPrefix('productcarousel_');
		$form->setFieldNameSuffix('productcarousel');
		$this->setForm($form);
		$fieldset = $form->addFieldset('productcarousel_form', array('legend'=>Mage::helper('productcarousel')->__('Carousel setting')));

                $fieldset->addField('showblocktitle', 'select', array(
			'label' => Mage::helper('productcarousel')->__('Show block title'),
			'name'  => 'showblocktitle',

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
		$fieldset->addField('block_title_color', 'text', array(
			'label' => Mage::helper('productcarousel')->__('Block title color'),
			'name'  => 'block_title_color',
			'note'	=> $this->__('Color code (Ex: 686868)'),
			'class'     => 'color {required:false, adjust:false, hash:false}',
		));
        
        
		$fieldset->addField('block_title_bg_color', 'text', array(
			'label' => Mage::helper('productcarousel')->__('Block title background color'),
			'name'  => 'block_title_bg_color',
			'note'	=> $this->__('Color code (Ex: 686868)'),
			'class'     => 'color {required:false, adjust:false, hash:false}',
                        'note' => $this->__('Set blank to inherit'),
		));
        
                 $fieldset->addField('shownavigator', 'select', array(
			'label' => Mage::helper('productcarousel')->__('Show navigator'),
			'name'  => 'shownavigator',

			'values'=> array(
				array(
					'value' => '2',
					'label' => Mage::helper('productcarousel')->__('Top'),
				),
				array(
					'value' => '1',
					'label' => Mage::helper('productcarousel')->__('Middle'),
				),
				array(
					'value' => '0',
					'label' => Mage::helper('productcarousel')->__('No'),
				),
			),
            'value'=>'1',
		));
		$fieldset->addField('skin', 'select', array(
			'label' => Mage::helper('productcarousel')->__('Navigator Skin'),
			'name'  => 'skin',

			'values'=> array(
				array(
					'value' => 'blue',
					'label' => Mage::helper('productcarousel')->__('Blue'),
				),
                                 array(
					'value' => 'cyan',
					'label' => Mage::helper('productcarousel')->__('Cyan'),
				),
				array(
					'value' => 'gray',
					'label' => Mage::helper('productcarousel')->__('Gray'),
				),
                                array(
					'value' => 'orange',
					'label' => Mage::helper('productcarousel')->__('Orange'),
				),
                                array(
					'value' => 'pink',
					'label' => Mage::helper('productcarousel')->__('Pink'),
				),
                                array(
					'value' => 'red',
					'label' => Mage::helper('productcarousel')->__('Red'),
				),
                                array(
					'value' => 'violet',
					'label' => Mage::helper('productcarousel')->__('Violet'),
				),
                                array(
					'value' => 'yellow',
					'label' => Mage::helper('productcarousel')->__('Yellow'),
				),
			),
                        'value'=>'gray',
                    ));
                
                $fieldset->addField('showpagination', 'select', array(
			'label' => Mage::helper('productcarousel')->__('Show pagination'),
			'name'  => 'showpagination',

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
                
                $fieldset->addField('autoheight', 'select', array(
			'label' => Mage::helper('productcarousel')->__('Auto height'),
			'name'  => 'autoheight',
			'required'  => false,
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
                
                
                 
                $fieldset->addField('slidespeed', 'text', array(
			'label' => Mage::helper('productcarousel')->__('Slide speed'),
			'name'  => 'slidespeed',
			'required'  => false,
			'class' => 'validate-number',
            'note' => $this->__('Slide speed in milliseconds. Default: 200ms.'),
            'value' => '200',
		));
        $fieldset->addField('paginationspeed', 'text', array(
			'label' => Mage::helper('productcarousel')->__('Pagination speed'),
			'name'  => 'paginationspeed',
			'required'  => false,
			'class' => 'validate-number',
            'note' => $this->__('Pagination speed in milliseconds. Default: 800ms.'),
            'value' => '800',
		));
         $fieldset->addField('rewindspeed', 'text', array(
			'label' => Mage::helper('productcarousel')->__('Rewind speed'),
			'name'  => 'rewindspeed',
			'required'  => false,
			'class' => 'validate-number',
            'note' => $this->__('Rewind speed in milliseconds. Default: 1000ms'),
            'value' => '1000',
		));
                
                
        $fieldset->addField('direction', 'select', array(
                'label' => Mage::helper('productcarousel')->__('View Mode'),
                'name'  => 'direction',

                'values'=> array(
                        array(
                                'value' => 'hori',
                                'label' => Mage::helper('productcarousel')->__('Horizontal'),
                        ),
                        array(
                                                'value' => 'vert',
                                                'label' => Mage::helper('productcarousel')->__('Vertical'),
                                        ),
                                ),
                    'value'=>'horizontal',
		));
         $fieldset->addField('numberproductshow', 'text', array(
			'label' => Mage::helper('productcarousel')->__('Number of products to show'),
			'name'  => 'numberproductshow',
			'required'  => false,
			'class' => 'validate-number',
            'note' => $this->__('Only use for Vertical carousel view mode.'),
            
		));
                
                
                
                $fieldset->addField('autoplay', 'text', array(
			'label' => Mage::helper('productcarousel')->__('Auto play'),
			'name'  => 'autoplay',
			'class' => 'validate-number',
                    'note' => $this->__('Turn on autoplay with number miliseceond.Example: 5000'),
            'value'=>'0',
		));
        
        $fieldset->addField('pauseonhover', 'select', array(
			'label' => Mage::helper('productcarousel')->__('Stop on hover'),
			'name'  => 'pauseonhover',
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
            'note' => $this->__('Stop autoplay on mouse hover'),
            'value'=>'1',
		));
        
                
                $fieldset->addField('swipeontouch', 'select', array(
			'label' => Mage::helper('productcarousel')->__('Swipe on touch'),
			'name'  => 'swipeontouch',
			'values'=> array(
				array(
					'value' => '1',
					'label' => Mage::helper('productcarousel')->__('On'),
				),
				array(
					'value' => '0',
					'label' => Mage::helper('productcarousel')->__('Off'),
				),
			),
            'note' => $this->__('Turn off/on touch events.'),
            'value'=>'1',
		));
                
                 $fieldset->addField('swipeonmouse', 'select', array(
			'label' => Mage::helper('productcarousel')->__('Swipe on mouse'),
			'name'  => 'swipeonmouse',
			'values'=> array(
				array(
					'value' => '1',
					'label' => Mage::helper('productcarousel')->__('On'),
				),
				array(
					'value' => '0',
					'label' => Mage::helper('productcarousel')->__('Off'),
				),
			),
            'note' => $this->__('Turn off/on mouse events.'),
            'value'=>'1',
		));
                
                $fieldset->addField('customconfig', 'textarea', array(
                                'label' => Mage::helper('productcarousel')->__('Custom config'),
                                'name'  => 'customconfig',

                    'note' => $this->__('Customize configuration for Carousel.<a href="http://www.magpassion.com/documentation/mp_productcarousel/">See how to...</a>'),

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