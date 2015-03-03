<?php

class Zolago_Campaign_Model_Campaign_Validator extends Zolago_Common_Model_Validator_Abstract
{

    protected function _getHelper()
    {
        return Mage::helper('zolagocampaign');
    }

    public function validate($data)
    {

        $this->_errors = array();
        $this->_data = $data;

        //validate
        if (!Zend_Validate::is($data['date_from'], 'NotEmpty') && !Zend_Validate::is($data['date_to'], 'NotEmpty')) {
            $this->_errors[] = $this->_helper->__('Please enter start date or end date');
        }

        return $this->_errors;
    }

}
