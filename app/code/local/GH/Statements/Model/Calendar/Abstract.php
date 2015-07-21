<?php
/**
 *   Calendar for statements
 */
class GH_Statements_Model_Calendar_Abstract extends Mage_Core_Model_Abstract {
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

}

