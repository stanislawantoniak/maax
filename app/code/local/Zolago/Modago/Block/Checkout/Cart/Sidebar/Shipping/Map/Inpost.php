<?php

/**
 * Class Zolago_Modago_Block_Checkout_Cart_Sidebar_Shipping_Map
 */
class Zolago_Modago_Block_Checkout_Cart_Sidebar_Shipping_Map_Inpost
    extends Zolago_Modago_Block_Checkout_Cart_Sidebar_Shipping
{

    public static function getPopulateMapData()
    {
        $result = array(
            'map_points' => "",
            'filters' => array(),
        );


        $collection = Mage::getModel("ghinpost/locker")->getCollection();
        $collection->addFieldToFilter("type", GH_Inpost_Model_Locker::TYPE_PACK_MACHINE);
        $collection->addFieldToFilter("is_active", GH_Inpost_Model_Locker::STATUS_ACTIVE);
        $collection->addOrder("town", "ASC");

        if ($collection->count() == 0)
            return $result;


        $lockers = array();
        $filters = array();

        foreach ($collection as $locker) {
            /* @var $locker GH_Inpost_Model_Locker */
            $townName = (string)ucwords(strtolower($locker->getTown()));

            $filters[$townName][$locker->getPostcode()][$locker->getName()] = $locker->getData();

            $additional = array(
                $locker->getStreet() . " " . $locker->getBuildingNumber(),
                $locker->getPostcode() . " " . $townName,
                "(" . $locker->getLocationDescription() . ")"
            );
            $lockers[] = array(
                "id" => $locker->getId(),
                "name" => $locker->getName(),
                'street' => htmlentities((string)$locker->getStreet()),
                'building_number' => htmlentities((string)$locker->getBuildingNumber()),
                "postcode" => (string)$locker->getPostcode(),
                'town' => htmlentities($townName),
                "location_description" => htmlentities((string)$locker->getLocationDescription()),
                "longitude" => (string)$locker->getLongitude(),
                "latitude" => (string)$locker->getLatitude(),
                "additional" => htmlentities(implode("<br />", $additional))
            );
        }
        if (!empty($lockers)) {
            $result["map_points"] = json_encode($lockers,JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP);
        }

        $oldLocal = setlocale(LC_COLLATE, 'pl_PL.utf8');
        ksort($filters, SORT_LOCALE_STRING);
        setlocale(LC_COLLATE, $oldLocal);

        if (!empty($filters)) {
            $result["filters"] = $filters;
        } else {
            $result["filters"] = array();
        }


        return $result;
    }

}