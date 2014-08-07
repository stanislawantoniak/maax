<?php

class Zolago_Banner_Model_Banner_Type
{

    const BANNER_TYPE_SLIDER = "slider";
    const BANNER_TYPE_BOX = "box";
    const BANNER_TYPE_INSPIRATION = "inspiration";

    /**
     * @return array
     */
    public function toOptionHash()
    {
        return array(
            self::BANNER_TYPE_SLIDER => Mage::helper("zolagobanner")->__("Slider"),
            self::BANNER_TYPE_BOX => Mage::helper("zolagobanner")->__("Box"),
            self::BANNER_TYPE_INSPIRATION => Mage::helper("zolagobanner")->__("Inspiration")
        );
    }
}