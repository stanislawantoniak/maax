<?php

class Zolago_Customer_Model_Resource_Emailtoken_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract {
  
    protected function _construct() {
        parent::_construct();
        $this->_init('zolagocustomer/emailtoken');
    }
    
    public function setFilterToken($token) {	
        $this->getSelect()
            ->where('token = \''.$token.'\'');
        return $this;
    }
}
