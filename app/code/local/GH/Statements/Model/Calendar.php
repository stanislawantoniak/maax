<?php
/**
 *   Calendar for statements
 */
class GH_Statements_Model_Calendar extends Mage_Core_Model_Abstract {
    protected function _construct() {
        $this->_init('ghstatements/calendar');
        parent::_construct();
    }
    public function validate($data=null) {

        if($data===null){
            $data = $this->getData();
        }
        elseif($data instanceof Varien_Object){
            $data = $data->getData();
        }

        if(!is_array($data)){
            return false;
        }

        $errors = $this->getValidator()->validate($data);

        if (empty($errors)) {
            return true;
        }
        return $errors;

    }
    /**
     * @return GH_Statements_Model_Calendar_Validator
     */
    public function getValidator() {
        return Mage::getSingleton("ghstatements/calendar_validator");
    }

}

