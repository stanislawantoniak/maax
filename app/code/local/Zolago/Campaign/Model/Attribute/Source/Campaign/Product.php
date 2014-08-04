<?php

class Zolago_Campaign_Model_Attribute_Source_Campaign_Product extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{

    public function getAllOptions()
    {

        return array();
    }

    public function toOptionArray()
    {
        return $this->getAllOptions();
    }

}