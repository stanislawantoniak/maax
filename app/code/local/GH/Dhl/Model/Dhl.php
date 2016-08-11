<?php


class GH_Dhl_Model_Dhl extends Mage_Core_Model_Abstract {



	protected function _construct() {
		$this->_init('ghdhl/dhl');
	}
    /**
     * @param array $data
     * @return array
     */
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
     * @return GH_Dhl_Model_Dhl_Validator
     */
    public function getValidator() {
        return Mage::getSingleton("ghdhl/dhl_validator");
    }
}