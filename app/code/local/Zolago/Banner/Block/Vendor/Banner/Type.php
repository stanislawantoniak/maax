<?php

class Zolago_Banner_Block_Vendor_Banner_Type extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getTypes()
    {
        $types = array_merge(
            array(0 => "    "),
            Mage::getSingleton('zolagobanner/banner_type')->toOptionHash()
        );
        return $types;
    }
}