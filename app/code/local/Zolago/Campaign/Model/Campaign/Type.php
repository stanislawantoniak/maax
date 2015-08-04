<?php

class Zolago_Campaign_Model_Campaign_Type
{

    const TYPE_SALE = "sale";
    const TYPE_PROMOTION = "promotion";
    const TYPE_INFO = "info";

    /**
     * @return array
     */
    public function toOptionHash()
    {
        return array(
            self::TYPE_SALE => Mage::helper("zolagocampaign")->__("Sale"),
            self::TYPE_PROMOTION => Mage::helper("zolagocampaign")->__("Promotion"),
            self::TYPE_INFO => Mage::helper("zolagocampaign")->__("Info")
        );
    }

}