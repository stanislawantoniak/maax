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
            $periods = array();
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
                    if (empty($valueItem['fuel_percent_date_to'])) {
                        Mage::getSingleton('adminhtml/session')
                            ->addError($helper->__('%s Fuel Charge date to can not be empty', $carrierLabel));
                        return;
                    }
                    if (strtotime($valueItem['fuel_percent_date_to']) < strtotime($valueItem['fuel_percent_date_from'])) {
                        Mage::getSingleton('adminhtml/session')
                            ->addError($helper->__('%s Fuel Charge Settings: Date to can not be earlier than Date from', $carrierLabel));
                        return;

                    }
                    $periods[$n] = array("from" => $valueItem['fuel_percent_date_from'], "to" => $valueItem['fuel_percent_date_to']);
                }

            }


            if (!empty($periods)) {
                foreach ($periods as $period) {
                    foreach ($periods as $periodToCompare) {
                        if($period !== $periodToCompare){
                            $result = $this->intersects($period["from"], $period["to"], $periodToCompare["from"], $periodToCompare["to"]);

                            if($result){
                                $from1 = $period["from"];
                                $from2 = $periodToCompare["from"];
                                $to1 = $period["to"];
                                $to2 = $periodToCompare["to"];
                                Mage::getSingleton('adminhtml/session')
                                    ->addError($helper->__('%s Fuel Charge Settings: Dates period %s - %s  overlapped with period  %s - %s', $carrierLabel, $from1, $to1, $from2, $to2));
                                return;
                            }
                        }

                    }
                }
            }
        }

        return parent::save();  //call original save method so whatever happened
        //before still happens (the value saves)
    }

    private function intersects($start1, $end1, $start2, $end2)
    {
        return ($start1 == $start2) || ($start1 > $start2 ? $start1 <= $end2 : $start2 <= $end1);
    }
}
