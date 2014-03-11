<?php
class Zolago_Eav_Model_Observer
{
	public function addExtraSettings($observer)
	{
		$fieldset = $observer->getForm()->getElement('base_fieldset');
		$yesnoSource = Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray();

		$fieldset->addField('set_id', 'select', array(
			'name'      => 'set_id',
			'label'     => Mage::helper('zolagoeav')->__('Default Attribute Set'),
			'title'     => Mage::helper('zolagoeav')->__('Default Attribute Set'),
			'values'    => Mage::getModel('zolagoeav/entity_attribute_source_set')->getAllOptions(),
			'required'	=> true,
		));
		
		$fieldset->addField('add_to_set', 'checkbox', array(
			'name'      => 'add_to_set',
			'onclick'   => 'this.value = this.checked ? 1 : 0;',
			'label'     => Mage::helper('zolagoeav')->__('Add to Attribute Set'),
			'title'     => Mage::helper('zolagoeav')->__('Add to Attribute Set'),
			'values'    => $yesnoSource,
		));
		
		$fieldset->addField('is_mappable', 'select', array(
			'name'      => 'is_mappable',
			'label'     => Mage::helper('zolagoeav')->__('Use in Category Mapping'),
			'title'     => Mage::helper('zolagoeav')->__('Use in Category Mapping'),
			'values'    => $yesnoSource,
		));
	}
	
	public function addAttributeToSet($observer)
	{
		/** @var $session Mage_Admin_Model_Session */
		$session = Mage::getSingleton('adminhtml/session');
		$attribute = $observer->getAttribute();
		
		if ($attribute->getAddToSet() && $attribute->getSetId()) {
			$setup = Mage::getModel('eav/entity_setup', 'core_setup');
			$attributeId	= $attribute->getId();
			$attributeSetId = $attribute->getSetId();

			//Get "General" attribute group info
			$attributeGroupId = $setup->getAttributeGroupId('catalog_product', $attributeSetId, 'General');
			try {
				$setup->addAttributeToSet('catalog_product', $attributeSetId, $attributeGroupId, $attributeId);
			} catch (Exception $e) {
				Mage::logException($e);
				$session->addError(
					Mage::helper('zolagoeav')->__('Error while adding %s attribute to Attribute Set', $attribute->getFrontendLabel())
				);
			}
		}
	}
}
