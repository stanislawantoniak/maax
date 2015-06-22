<?php


class Orba_Shipping_Model_System_Backend_FuelChargeConfig extends Mage_Core_Model_Config_Data
{
    public function setValue($value)
    {
        $value = $this->_unserialize($value);
        if (is_array($value)) {
            unset($value['$$ROW']);
        }
        $this->setData('value', $value);
        return $this;
    }
    protected function _afterLoad()
    {
        if (!is_array($this->getValue())) {
            $value = $this->getValue();
            $this->setData('value', $this->_unserialize($value));
        }
    }

    protected function _beforeSave()
    {
        if (is_array($this->getValue())) {
            $this->setData('value', $this->_serialize($this->getValue()));
        }
    }

    protected function _serialize($value)
    {
        return Mage::helper('udropship')->serialize($value);
    }
    protected function _unserialize($value)
    {
        return Mage::helper('udropship')->unserialize($value);
    }

    public function save()
    {
        $value = $this->getValue(); //get the value from our config

        if(!empty($value)){
            foreach($value as $n => $valueItem){
                if($n !== "group"){
                    if (empty($valueItem['fuel_percent'])) {
                        Mage::getSingleton('adminhtml/session')->addError("Fuel Charge % can not be empty");
                        return;
                    }
                    if (empty($valueItem['fuel_percent_date_from'])) {
                        Mage::getSingleton('adminhtml/session')->addError("Fuel Charge date from can not be empty");
                        return;
                    }
                    if (empty($valueItem['fuel_percent_date_to'])) {
                        Mage::getSingleton('adminhtml/session')->addError("Fuel Charge date to can not be empty");
                        return;
                    }
                    if (strtotime($valueItem['fuel_percent_date_to']) <= strtotime($valueItem['fuel_percent_date_from'])) {
                        Mage::getSingleton('adminhtml/session')->addError("Fuel Charge: Date to can not be earlier than Date from");
                        return;

                    }
                }

            }
        }

        return parent::save();  //call original save method so whatever happened
        //before still happens (the value saves)
    }
}
