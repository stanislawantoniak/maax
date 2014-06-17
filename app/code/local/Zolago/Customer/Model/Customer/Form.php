<?php

class Zolago_Customer_Model_Customer_Form extends Mage_Customer_Model_Form {
    /**
     * Validate data array and return true or array of errors
     *
     * @param array $data
     * @return boolean|array
     */
    public function validateData(array $data)
    {
        //echo 'Zolago_Customer_Model_Customer_Form';
        //perform parent validation
        $result = parent::validateData($data);

        //checking sipping address; perform additional validation
        //echo $this->getEntity()->getAddressType();
        if ($this->getEntity()->getAddressType() == Mage_Sales_Model_Quote_Address::TYPE_SHIPPING) {
            //echo $data['postcode'];
            $valid = $this->_validateDHLZip($data);
            if ($result !== true && $valid !== true) {
                $result[] = $valid;
            } elseif ($result === true && $valid !== true) {
                $result = $valid;
            }
        }

        return $result;
    }

    /**
     * @param $data
     *
     * @return array|bool
     */
    protected function _validateDHLZip($data)
    {
        $errors = array();
        if (!empty($data['postcode'])) {
            $attribute = $this->getAttribute('postcode');
            $dhlHelper = Mage::helper('zolagodhl');
            $dhlValidZip = $dhlHelper->isDHLValidZip($data['postcode']);

            if (!$dhlValidZip) {
                $label = Mage::helper('eav')->__($attribute->getStoreLabel());
                $errors[] = Mage::helper('eav')->__('Invalid zip', $label);
            }

        }
        if (empty($errors)) {
            return true;
        }
        return $errors;
    }
}