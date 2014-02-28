<?php

/**
 * Description of Form
 */
class Zolago_Mapper_Block_Adminhtml_Mapper_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {

	protected function _prepareForm() {
		$helper = Mage::helper('zolagomapper');
		$model = $this->getDataObject();
		
		$form = new Varien_Data_Form(array(
			'id' => 'edit_form',
			'action' => $this->getData('action'),
			'method' => 'post'
		));

		$fieldset = $form->addFieldset('base_fieldset', array(
			'legend' => $helper->__('Mapping Information'),
		));

		if(!$this->_getAttributeSetId()){
			$select = $fieldset->addField('attribute_set_id', 'select', array(
				'name' => 'attribute_set_id',
				'label' => Mage::helper('eav')->__('Attribute set'),
				'required' => true,
				'values' => $this->_getAttributeSetOptions()
			));
		}else{
			// Set model value for new object
			if($this->_isNew()){
				$model->setAttributeSetId($this->_getAttributeSetId());
			}
			$select = $fieldset->addField('attribute_set_id_disabled', 'select', array(
				'name' => 'attribute_set_id',
				'label' => Mage::helper('eav')->__('Attribute set'),
				'required' => true,
				'disabled' => true,
				'values' => $this->_getAttributeSetOptions(),
			));
			
			// Add hidden field
			$fieldset->addField('attribute_set_id', 'hidden', array(
				'name' => 'attribute_set_id'
			));

			$fieldset->addField('name', 'text', array(
				'name' => 'priority',
				'required' => true,
				'label' => $helper->__('Name'),
			));

			/**
			 * Website ids
			 */
			if (!Mage::app()->isSingleStoreMode()) {
				$fieldset->addField('website_id', 'select', array(
					'name' => 'website_id',
					'label' => Mage::helper('cms')->__('Website'),
					'required' => true,
					'values' => Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash()
				));
			} else {
				$fieldset->addField('website_id', 'hidden', array(
					'name' => 'website_id',
					'value' => Mage::app()->getStore(true)->getId()
				));
				$model->setStoreId(Mage::app()->getStore(true)->getId());
			}

			$fieldset->addField('priority', 'text', array(
				'name' => 'priority',
				'label' => $helper->__('Priority'),
				'class' => 'validate-digits'
			));

			// Rules
			$renderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
				->setTemplate('promo/fieldset.phtml')
				->setNewChildUrl($this->getUrl('*/mapper/newConditionHtml/form/conditions_fieldset'));

			$fieldset = $form->addFieldset('conditions_fieldset', array(
				'legend'=>Mage::helper('catalogrule')->__('Conditions (leave blank for all products)'))
			);		
			$fieldset->setRenderer($renderer);

			$rules = $fieldset->addField('conditions', 'text', array(
				'name' => 'conditions',
				'label' => Mage::helper('catalogrule')->__('Conditions'),
				'required' => true,
			));
			$rules->setRule($model);
			$rules->setRenderer(Mage::getBlockSingleton('rule/conditions'));

			$fieldset = $form->addFieldset('action_fieldset', array(
				'legend' => $helper->__('Actions'),
			));
			$categoryHtml = $this->getLayout()->
					createBlock('zolagomapper/adminhtml_mapper_edit_form_categories')->
					setMapper($model)->
					toHtml();

			$categories = $fieldset->addField('category_ids_as_string', 'hidden', array(
				'name' => 'category_ids_as_string',
				'label' => Mage::helper('catalogrule')->__('Categories'),
				'after_element_html' => $categoryHtml
			));
		}
		
		
		
		$form->setValues($model->getData());
		if($this->_getAttributeSetId()){
			$select->setValue($this->_getAttributeSetId());
		}
		$this->setForm($form);
		return parent::_prepareForm();
	}

	protected function	_getAttributeSetOptions(){
		return Mage::getSingleton('zolagomapper/system_eav_entity_attribute_set')->toOptionHash();
	}
	
	protected function _isNew() {
		return !(int)$this->getDataObject()->getId();
	}
	
	protected function _getAttributeSetId(){
		if($this->_isNew()){
			return Mage::app()->getRequest()->getParam("attribute_set_id");
		}
		return $this->getDataObject()->getAttributeSetId();
	}
}

?>
