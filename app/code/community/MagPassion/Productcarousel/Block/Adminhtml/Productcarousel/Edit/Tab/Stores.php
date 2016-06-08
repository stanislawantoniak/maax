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
 * store selection tab
 *
 * @category	MagPassion
 * @package		MagPassion_Productcarousel
 * @author MagPassion.com
 */
class MagPassion_Productcarousel_Block_Adminhtml_Productcarousel_Edit_Tab_Stores extends Mage_Adminhtml_Block_Widget_Form{
	/**
	 * prepare the form
	 * @access protected
	 * @return MagPassion_Productcarousel_Block_Adminhtml_Productcarousel_Edit_Tab_Stores
	 * @author MagPassion.com
	 */
	protected function _prepareForm(){
		$form = new Varien_Data_Form();
		$form->setFieldNameSuffix('productcarousel');
		$this->setForm($form);
		$fieldset = $form->addFieldset('productcarousel_stores_form', array('legend'=>Mage::helper('productcarousel')->__('Store views')));
		$field = $fieldset->addField('store_id', 'multiselect', array(
			'name'  => 'stores[]',
			'label' => Mage::helper('productcarousel')->__('Store Views'),
			'title' => Mage::helper('productcarousel')->__('Store Views'),
			'required'  => true,
			'values'=> Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
		));
		$renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
		$field->setRenderer($renderer);
  		$form->addValues(Mage::registry('current_productcarousel')->getData());
		return parent::_prepareForm();
	}
}