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
 * Product Carousel admin edit block
 *
 * @category	MagPassion
 * @package		MagPassion_Productcarousel
 * @author MagPassion.com
 */
class MagPassion_Productcarousel_Block_Adminhtml_Productcarousel_Edit extends Mage_Adminhtml_Block_Widget_Form_Container{
	/**
	 * constuctor
	 * @access public
	 * @return void
	 * @author MagPassion.com
	 */
	public function __construct(){
		parent::__construct();
		$this->_blockGroup = 'productcarousel';
		$this->_controller = 'adminhtml_productcarousel';
		$this->_updateButton('save', 'label', Mage::helper('productcarousel')->__('Save Product Carousel'));
		$this->_updateButton('delete', 'label', Mage::helper('productcarousel')->__('Delete Product Carousel'));
		$this->_addButton('saveandcontinue', array(
			'label'		=> Mage::helper('productcarousel')->__('Save And Continue Edit'),
			'onclick'	=> 'saveAndContinueEdit()',
			'class'		=> 'save',
		), -100);
		$this->_formScripts[] = "
			function saveAndContinueEdit(){
				editForm.submit($('edit_form').action+'back/edit/');
			}
		";
	}
	/**
	 * get the edit form header
	 * @access public
	 * @return string
	 * @author MagPassion.com
	 */
	public function getHeaderText(){
		if( Mage::registry('productcarousel_data') && Mage::registry('productcarousel_data')->getId() ) {
			return Mage::helper('productcarousel')->__("Edit Product Carousel '%s'", $this->htmlEscape(Mage::registry('productcarousel_data')->getBlocktitle()));
		} 
		else {
			return Mage::helper('productcarousel')->__('Add Product Carousel');
		}
	}
}