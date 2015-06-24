<?php

class Zolago_Payment_Model_Source_Channel_Owner
{
    const OWNER_MALL     = "1";
    const OWNER_VENDOR   = "2";

    public function toOptionArray()
    {
        $arr = array(
            array('value' => self::OWNER_MALL  , 'label' => 'Mall'),
            array('value' => self::OWNER_VENDOR, 'label' => 'Vendor'),
        );
        return $arr;
    }
}
