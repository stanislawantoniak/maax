<?php

/**
 * Class Zolago_Modago_Block_Checkout_Cart_Sidebar_Shipping_Map
 */
class Zolago_Modago_Block_Checkout_Cart_Sidebar_Shipping_Map_Inpost
    extends Zolago_Modago_Block_Checkout_Cart_Sidebar_Shipping
{

    public static function getPopulateMapData()
    {
        $result = array();


        $collection = Mage::getModel("ghinpost/locker")->getCollection();

        if ($collection->count() == 0) {
            return $result;
        }

        $lockers = array();
        $filters = array();

        foreach ($collection as $locker) {
            /* @var $locker GH_Inpost_Model_Locker */
            $filters[$locker->getTown()][$locker->getPostcode()][$locker->getName()] = $locker->getData();

            $additional = array(
                $locker->getStreet() . " " . $locker->getBuildingNumber(),
                $locker->getPostcode() . " " . $locker->getTown(),
                "(" . $locker->getLocationDescription() . ")"
            );
            $lockers[] = array(
                "id" => $locker->getId(),
                "name" => $locker->getName(),
                'street' => (string)$locker->getStreet(),
                'building_number' => (string)$locker->getBuildingNumber(),
                "postcode" => $locker->getPostcode(),
                'town' => (string)$locker->getTown(),
                "location_description" => htmlentities((string)$locker->getLocationDescription()),
                "longitude" => $locker->getLongitude(),
                "latitude" => $locker->getLatitude(),
                "additional" => htmlentities(implode("<br />", $additional))
            );
        }
        if (!empty($lockers)) {
            $result["map_points"] = json_encode($lockers, JSON_HEX_APOS);
        }

        if (!empty($filters)) {
            $result["filters"] = $filters;
        }


        return $result;
    }

}