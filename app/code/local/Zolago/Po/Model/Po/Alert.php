<?php

class Zolago_Po_Model_Po_Alert
{
    const ALERT_SAME_EMAIL_PO = 1;
    const ALERT_GH_API_RESERVATION_PROBLEM = 2;
    const ALERT_DHL_ZIP_CHECKING = 4;

    public static function getAllOptions()
    {
        $helper = Mage::helper('zolagopo');

        return array(
            self::ALERT_SAME_EMAIL_PO => $helper->__("Another order from same customer"),
            self::ALERT_GH_API_RESERVATION_PROBLEM => $helper->__("No reservation in vendor's system"),
            self::ALERT_DHL_ZIP_CHECKING => $helper->__("Zip code in shipment address is not valid.")
        );
    }

    public static function getAlertText($int)
    {
        switch ($int) {
            case self::ALERT_SAME_EMAIL_PO:
                return "There is another order from the same customer. Click the %s to see the customer order.";
                break;
            case self::ALERT_GH_API_RESERVATION_PROBLEM:
                return "Products reservations in vendor's system could not be made";
                break;
            case self::ALERT_DHL_ZIP_CHECKING:
                return "Zip code in shipment address is not valid. There will be a problem when shipping to that address.";
                break;
        }
        return "";
    }
}
