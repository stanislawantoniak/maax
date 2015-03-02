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
        //perform parent validation
        $result = parent::validateData($data);
        $dhlEnabled = Mage::helper('core')->isModuleEnabled('Orba_Shipping');
        $dhlActive = Mage::helper('orbashipping/carrier_dhl')->isActive();
        //checking sipping address; perform additional validation
        if ($dhlEnabled && $dhlActive) {
            //if ($this->getEntity()->getAddressType() == Mage_Sales_Model_Quote_Address::TYPE_SHIPPING) {
            $valid = $this->_validateDHLZip($data);
//            if ($result !== true && $valid !== true) {
//                $result[] = $valid;
//            } elseif ($result === true && $valid !== true) {
//                $result = $valid;
//            }
            //}
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
            $dhlHelper = Mage::helper('orbashipping/carrier_dhl');
            $dhlValidZip = $dhlHelper->isDHLValidZip($data['country_id'],$data['postcode']);

            if (!$dhlValidZip) {
                $label = Mage::helper('eav')->__($attribute->getStoreLabel());
                $errors[] = Mage::helper('eav')->__('Please enter valid DHL zip code', $label);
            }

        }
        if (empty($errors)) {
            return true;
        }
        return $errors;
    }
}