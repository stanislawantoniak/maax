<?php
class Zolago_Customer_Model_Observer {
    /**
     * Clear tokens older that limit
     */
    public function cleanOldTokens() {
        $today = new Zend_Date();
        echo Mage::getResourceModel("zolagocustomer/emailtoken")->cleanOldTokens(
                $today->subHour(Zolago_Customer_Model_Emailtoken::HOURS_EXPIRE)
        );
    }
}