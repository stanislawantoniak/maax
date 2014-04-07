<?php
class Zolago_Eav_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function isAttributeFilterable(Mage_Eav_Model_Entity_Attribute $attribute)
	{
		$filterable = false;
		if ($attribute->getGridPermission() >= Zolago_Eav_Model_Entity_Attribute_Source_GridPermission::USE_IN_FILTER) {
			$filterable = true;
		}
		return $filterable;
	}
	
	public function isAttributeVisible(Mage_Eav_Model_Entity_Attribute $attribute)
	{
		$visible = false;
		if ($attribute->getGridPermission() >= Zolago_Eav_Model_Entity_Attribute_Source_GridPermission::DISPLAY) {
			$visible = true;
		}
		return $visible;
	}
	
	public function isAttributeEditable(Mage_Eav_Model_Entity_Attribute $attribute)
	{
		$editable = false;
		if ($attribute->getGridPermission() >= Zolago_Eav_Model_Entity_Attribute_Source_GridPermission::EDITION) {
			$editable = true;
		}
		return $editable;
	}	
	
	public function isAttributeEditableNormal(Mage_Eav_Model_Entity_Attribute $attribute)
	{
		$editable = false;
		if ($attribute->getGridPermission() == Zolago_Eav_Model_Entity_Attribute_Source_GridPermission::EDITION) {
			$editable = true;
		}
		return $editable;
	}	
	
	public function isAttributeEditableInline(Mage_Eav_Model_Entity_Attribute $attribute)
	{
		$editable = false;
		if ($attribute->getGridPermission() == Zolago_Eav_Model_Entity_Attribute_Source_GridPermission::INLINE_EDITION) {
			$editable = true;
		}
		return $editable;
	}	
}