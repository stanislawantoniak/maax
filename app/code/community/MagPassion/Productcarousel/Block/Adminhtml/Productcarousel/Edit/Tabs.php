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
 * Product Carousel admin edit tabs
 *
 * @category	MagPassion
 * @package		MagPassion_Productcarousel
 * @author MagPassion.com
 */
class MagPassion_Productcarousel_Block_Adminhtml_Productcarousel_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs{
	/**
	 * constructor
	 * @access public
	 * @return void
	 * @author MagPassion.com
	 */
	public function __construct(){
		parent::__construct();
		$this->setId('productcarousel_tabs');
		$this->setDestElementId('edit_form');
		$this->setTitle(Mage::helper('productcarousel')->__('Product Carousel'));
	}
	/**
	 * before render html
	 * @access protected
	 * @return MagPassion_Productcarousel_Block_Adminhtml_Productcarousel_Edit_Tabs
	 * @author MagPassion.com
	 */
	protected function _beforeToHtml(){
		$this->addTab('form_productcarousel', array(
			'label'		=> Mage::helper('productcarousel')->__('Basic Options'),
			'title'		=> Mage::helper('productcarousel')->__('Basic Options'),
			'content' 	=> $this->getLayout()->createBlock('productcarousel/adminhtml_productcarousel_edit_tab_form')->toHtml(),
		));
                
        $this->addTab('form_style_productcarousel', array(
			'label'		=> Mage::helper('productcarousel')->__('Carousel setting'),
			'title'		=> Mage::helper('productcarousel')->__('Carousel setting'),
			'content' 	=> $this->getLayout()->createBlock('productcarousel/adminhtml_productcarousel_edit_tab_style')->toHtml(),
		));
        $this->addTab('form_display_productcarousel', array(
			'label'		=> Mage::helper('productcarousel')->__('Product Display Options'),
			'title'		=> Mage::helper('productcarousel')->__('Product Display Options'),
			'content' 	=> $this->getLayout()->createBlock('productcarousel/adminhtml_productcarousel_edit_tab_display')->toHtml(),
		));
        
		if (!Mage::app()->isSingleStoreMode()){
			$this->addTab('form_store_productcarousel', array(
				'label'		=> Mage::helper('productcarousel')->__('Store views'),
				'title'		=> Mage::helper('productcarousel')->__('Store views'),
				'content' 	=> $this->getLayout()->createBlock('productcarousel/adminhtml_productcarousel_edit_tab_stores')->toHtml(),
			));
		}
		$this->addTab('products', array(
			'label' => Mage::helper('productcarousel')->__('Associated products'),
			'url'   => $this->getUrl('*/*/products', array('_current' => true)),
   			'class'	=> 'ajax'
		));
		return parent::_beforeToHtml();
	}
}