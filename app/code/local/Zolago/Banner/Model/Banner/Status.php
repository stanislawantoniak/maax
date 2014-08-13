<?php
class Zolago_Banner_Model_Banner_Status {

    const BANNER_STATUS_ACTIVE = 1;
    const BANNER_STATUS_INACTIVE = 0;
    /**
     * @return array
     */
    public function toOptionHash()
    {
        return array(
            self::BANNER_STATUS_ACTIVE => Mage::helper("zolagobanner")->__("Active"),
            self::BANNER_STATUS_INACTIVE => Mage::helper("zolagobanner")->__("Inactive")
        );
    }
    
}