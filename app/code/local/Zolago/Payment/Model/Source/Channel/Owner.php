<?php

class Zolago_Payment_Model_Source_Channel_Owner
{
    const GH_VALUE     = 'GH';
    const VENDOR_VALUE = 'VENDOR';

    public function toOptionArray()
    {
        $arr = array(
            array('value' => self::GH_VALUE    , 'label' => 'Mall'),
            array('value' => self::VENDOR_VALUE, 'label' => 'Vendor'),
        );
        return $arr;
    }
}
