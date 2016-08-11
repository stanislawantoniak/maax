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
            $helper = Mage::helper("orbashipping");
            $group = $value["group"];

            $carrierLabel = "";
            switch ($group){
                case "orbadhl":
                    $carrierLabel = "Modago DHL - ";
                    break;
                case "orbaups":
                    $carrierLabel = "Modago UPS - ";
                    break;
            }

            foreach($value as $n => $valueItem){
                if($n !== "group"){
                    if (empty($valueItem['fuel_percent'])) {
                        Mage::getSingleton('adminhtml/session')
                            ->addError($helper->__('%s Fuel Charge percent can not be empty', $carrierLabel));
                        return;
                    }
                    if (empty($valueItem['fuel_percent_date_from'])) {
                        Mage::getSingleton('adminhtml/session')
                            ->addError($helper->__('%s Fuel Charge date from can not be empty', $carrierLabel));
                        return;
                    }

                }

            }

        }

        return parent::save();  //call original save method so whatever happened
        //before still happens (the value saves)
    }

    private function intersects($start1, $end1, $start2, $end2)
    {
        $start1 = strtotime($start1);
        $end1 = strtotime($end1);
        $start2 = strtotime($start2);
        $end2 = strtotime($end2);
        return ($start1 == $start2) || ($start1 > $start2 ? $start1 <= $end2 : $start2 <= $end1);
    }
}
