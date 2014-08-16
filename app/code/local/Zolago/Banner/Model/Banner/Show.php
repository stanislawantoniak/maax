<?php

class Zolago_Banner_Model_Banner_Show
{
    const BANNER_SHOW_IMAGE = 'image';
    const BANNER_SHOW_HTML = 'html';

    /**
     * @return array
     */
    public function toOptionHash()
    {
        return array(
            self::BANNER_SHOW_IMAGE => Mage::helper("zolagocampaign")->__("Image"),
            self::BANNER_SHOW_HTML => Mage::helper("zolagocampaign")->__("HTML")
        );
    }
}