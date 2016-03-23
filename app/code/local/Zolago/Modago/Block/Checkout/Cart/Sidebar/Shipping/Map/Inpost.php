<?php

/**
 * Class Zolago_Modago_Block_Checkout_Cart_Sidebar_Shipping_Map
 */
class Zolago_Modago_Block_Checkout_Cart_Sidebar_Shipping_Map_Inpost
    extends Zolago_Modago_Block_Checkout_Cart_Sidebar_Shipping
{

    public static function getPopulateMapData()
    {
        $result = "";
        $lockers = array();
        $collection = Mage::getModel("ghinpost/locker")->getCollection();
        //$collection->setPageSize(10);
        foreach ($collection as $locker) {
            /* @var $locker GH_Inpost_Model_Locker */
            $additional = array(
                $locker->getStreet() . " " . $locker->getBuildingNumber(),
                $locker->getPostcode() . " " . $locker->getTown(),
                $locker->getLocationDescription()
            );
            $lockers[] = array(
                "id" => $locker->getId(),
                "name" => $locker->getName(),
                'street' => $locker->getStreet(),
                "longitude" => $locker->getLongitude(),
                "latitude" => $locker->getLatitude(),
                "additional" => htmlentities(implode("<br />", $additional))
            );
        }
        if (!empty($lockers)) {
            $result = json_encode($lockers, JSON_HEX_APOS);
        }

        return $result;
    }

}