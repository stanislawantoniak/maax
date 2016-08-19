<?php

class Zolago_Modago_InpostController extends Mage_Core_Controller_Front_Action
{
    public function getPopulateMapDataAction()
    {
        $town = $this->getRequest()->getParam("town", "");
        $result = array();

        /* @var $collection GH_Inpost_Model_Resource_Locker_Collection */
        $collection = Mage::getModel("ghinpost/locker")->getCollection();
        if ($town != "") $collection->addFieldToFilter("town", array("eq" => $town));
        $collection->addFieldToFilter("type", GH_Inpost_Model_Locker::TYPE_PACK_MACHINE);
        $collection->addFieldToFilter("is_active", GH_Inpost_Model_Locker::STATUS_ACTIVE);

        $collection->addOrder("street", "ASC");

        $lockers = array();
        $streets = array();

        foreach ($collection as $locker) {
            /* @var $locker GH_Inpost_Model_Locker */
            $townName = (string)ucwords(strtolower($locker->getTown()));

            $lockers[] = array(
                "id" => $locker->getId(),
                "name" => $locker->getName(),
                'street' => (string)$locker->getStreet(),
                'building_number' => (string)$locker->getBuildingNumber(),
                "postcode" => (string)$locker->getPostcode(),
                'town' => $townName,
                "payment_point_description" => (string)$locker->getPaymentPointDescription(),
                "location_description" => htmlentities((string)$locker->getLocationDescription()),
                "longitude" => $locker->getLongitude(),
                "latitude" => $locker->getLatitude()
            );

            $streets[$locker->getName()] = (string)$locker->getStreet(). " " . (string)$locker->getBuildingNumber();
        }
        if (!empty($lockers)) {
            $result["map_points"] = $lockers;
        }

        if (!empty($streets)) {
            $result["filters"] = $streets;
        }


        echo json_encode($result, JSON_HEX_APOS);
        exit;
    }
}