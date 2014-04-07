<?php

class Zolago_Adminhtml_Block_Widget_Grid_Column_Filter_Multiselect extends 
	Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select
{
    public function getCondition()
    {
		if (is_null($this->getValue())) {
            return null;
        }
		if($this->getColumn()->getAttribute()){
			/**
			 * Do id by MySQL RegExp expression in applaying filter in gird
			 */
			$collection = $this->getColumn()->getGrid()->getCollection();
			/* @var $collection Mage_Catalog_Model_Resource_Product_Collection */

			$code = $this->getColumn()->getIndex();
			$collection->joinAttribute($code, "catalog_product/$code", "entity_id", null, "left");

			$valueTable1 = "at_".$code."_default";
			$valueTable2 = "at_".$code;

			if($collection->getStoreId()){
				$valueExpr = $collection->getSelect()->getAdapter()
					->getCheckSql("{$valueTable2}.value_id > 0", "{$valueTable2}.value", "{$valueTable1}.value");

			}else{
				$valueExpr = "$valueTable2.value";
			}
			// Try use regexp to match vales with boundary (like comma, ^, $)  - (123,456,678) 
			$collection->getSelect()->where(
					$valueExpr." REGEXP ?", "[[:<:]]".$this->getValue()."[[:>:]]"
			);

			return null;
		}
		array('like' => '%'.$this->getValue().'%');
    }
}