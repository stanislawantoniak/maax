<?php
class Zolago_Solrsearch_Block_Faces_Enum_Manadev
	extends Zolago_Solrsearch_Block_Faces_Enum_Abstract
{
	public function getManaFilter() {
		if(!$this->hasData("mana_filter")){
			$manaFilter = Mage::getModel("mana_filters/filter2");
			/* @var $manaFilter Mana_Filters_Model_Filter2 */
			$manaFilter->load($this->getAttributeCode(), "code");
			$this->setData("mana_filter", $manaFilter);
		}
		return $this->getData("mana_filter");
	}
	
	public function _prepareValuesCollection() {
		$coll  = $this->_getValueCollecitonModel();
		$coll->addFieldToFilter("filter_id", $this->getManaFilter()->getId());
		return $coll;
	}
	
	
	public function getByOptionIdMap($idx=null) {
		if(!$this->getData("by_option_id_map", $idx)){
			$out = array();
			foreach($this->getValuesCollection() as $value){
				if($value->getOptionId()){
					$out[$value->getOptionId()] = $value;
				}
			}
			$this->setData("by_option_id_map", $out);
		}
		return $this->getData("by_option_id_map", $idx);
		
	}
	
	/**
	 * @return Mana_Filters_Resource_Filter2_Value_Collection
	 */
	public function getValuesCollection() {
		if(!$this->getData("values_collection")){
			$this->setData("values_collection", $this->_prepareValuesCollection());
		}
		return $this->getData("values_collection");
	}
	
	protected function _getValueCollecitonModel() {
		$model = Mage::getResourceModel(
					'mana_filters/filter2_value_' . 
					(Mage::helper('mana_admin')->isGlobal() ? "" : "store_") . 
					"collection"
		);
		if(!Mage::helper('mana_admin')->isGlobal()){
			$model->addFieldToFilter("store_id", Mage::app()->getStore()->getId());
		}
		return $model;
	}


	protected function _getOptionIdByLabel($label) {
		foreach($this->getAllOptions() as $option){
			if($option['label']==$label){
				return $option['value'];
			}
		}
		return null;
	}
	
	/**
	 * @param type $item
	 * @return Mana_Filters_Model_Filter2
	 */
	public function getValueObject($item) {
		$optionId = $this->_getOptionIdByLabel($item);
		return $this->getByOptionIdMap($optionId);
	}

	
	public function getCanShowItem($item){
		if($this->getValueObject($item['item'])){
			return parent::getCanShowItem($item);
		}
		return false;
	}

}
