<?php

class Zolago_Eav_Model_Entity_Attribute_Source_Set extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    /**
     * Get Attribute Set Options
     * 
     * @param booloean $withEmpty Add empty Value
     * 
     * @return array Array with Attribute Set Options
     */
    public function getAllOptions($withEmpty = true)
    {
		if (is_null($this->_options)) {
			$entityType = Mage::getModel('catalog/product')->getResource()->getTypeId();
			$this->_options = Mage::getResourceModel('eav/entity_attribute_set_collection')
								->setEntityTypeFilter($entityType)
								->toOptionArray();
		}
		
        $options = $this->_options;

        if ($withEmpty) {
            array_unshift($options, array('label' => '', 'value' => ''));
        }
        return $options;
    }

}