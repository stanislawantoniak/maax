<?php

/**
 * Description of Form
 */
class Zolago_Mapper_Block_Adminhtml_Mapper_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {

	protected function _prepareForm() {
		$helper = Mage::helper('zolagomapper');
		$model = $this->getDataObject();
		$model->getCategoryIdsAsString();
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
			
			$fieldset->addField('do_run', 'hidden', array(
				'name' => 'do_run',
				'value' => 0
			));


            $fieldset->addField('do_saveAndQueue', 'hidden', array(
                'name' => 'do_saveAndQueue',
                'value' => 0
            ));

			$fieldset->addField('name', 'text', array(
				'name' => 'name',
				'required' => true,
				'label' => $helper->__('Name'),
			));
			
			$fieldset->addField('is_active', 'select', array(
				'name'          => 'is_active',
				'label'         => $helper->__('Is active'),
				'required'      => true,
				'options'       => Mage::getSingleton("adminhtml/system_config_source_yesno")->toArray()
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
					'value' => Mage::app()->getStore(true)->getWebsite()->getId()
				));
				$model->setWebsiteId(Mage::app()->getStore(true)->getWebsite()->getId());
			}

			$fieldset->addField('priority', 'text', array(
				'name' => 'priority',
				'label' => $helper->__('Priority'),
				'class' => 'validate-digits'
			));

			// Rules
			$condParams = array();
			if($this->_isNew()){
				$condParams['attribute_set_id'] = $this->_getAttributeSetId();
			}else{
				$condParams['mapper_id'] = $model->getId();
			}
			$renderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
				->setTemplate('promo/fieldset.phtml')
				->setNewChildUrl($this->getUrl('*/mapper/newConditionHtml/form/conditions_fieldset', $condParams));


			$fieldset = $form->addFieldset('conditions_fieldset', array(
				'legend'=>$helper->__('Conditions (leave blank for all products of attribute set)'))
			);
			$fieldset->setRenderer($renderer);
			
			$rules = $fieldset->addField('conditions', 'text', array(
				'name' => 'conditions',
				'label' => $helper->__('Conditions'),
				'required' => true,
			));
			$rules->setRule($model);
			$rules->setRenderer(Mage::getBlockSingleton('rule/conditions'));

			// Category
			$fieldset = $form->addFieldset('action_fieldset', array(
				'legend' => $helper->__('Categories to be assigned'),
			));
			$categoryHtml = $this->getLayout()->
					createBlock('zolagomapper/adminhtml_mapper_edit_form_categories')->
					setMapper($model)->
					toHtml();

			$categories = $fieldset->addField('category_ids_as_string', 'hidden', array(
				'name' => 'category_ids_as_string',
				'after_element_html' => $categoryHtml
			));
		}
		
		
		
		$form->setValues($model->getData());
		if($this->_getAttributeSetId()){
			$select->setValue($this->_getAttributeSetId());
		}
        $form->setUseContainer(true);// Remove
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
