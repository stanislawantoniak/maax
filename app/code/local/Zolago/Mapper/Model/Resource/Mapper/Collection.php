<?php

class Zolago_Mapper_Model_Resource_Mapper_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract {
  
    protected function _construct() {
        parent::_construct();
        $this->_init('zolagomapper/mapper');
    }
	
	/**
	 * @return Zolago_Mapper_Model_Resource_Mapper_Collection
	 */
	public function joinAttributeSet() {
		return $this;
	}
	
}
