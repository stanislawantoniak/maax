<?php

/**
 * Description of Form
 */
class Zolago_Adminhtml_Block_Catalog_Category_Filters_Form 
	extends Mage_Adminhtml_Block_Widget_Form {

	protected function _prepareForm() {
		$helper = Mage::helper('zolagoadminhtml');
		$model = $this->getDataObject();
		$model->getCategoryIdsAsString();
		$form = new Varien_Data_Form(array(
			'id' => 'edit_form',
			'action' => $this->getData('action'),
			'method' => 'post'
		));

		$form->setValues($model->getData());
        $form->setUseContainer(true);// Remove
		$this->setForm($form);
		return parent::_prepareForm();
	}
}

?>
