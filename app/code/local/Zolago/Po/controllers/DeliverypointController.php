<?php

class Zolago_Po_DeliverypointController extends Mage_Core_Controller_Front_Action
{
    public function getInpostDataAction()
    {
        $town = $this->getRequest()->getParam("town", "");
        $result = array();

        /* @var $collection GH_Inpost_Model_Resource_Locker_Collection */
        $collection = Mage::getModel("ghinpost/locker")->getCollection();
        $collection->addFieldToFilter("town", array("eq" => $town));
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
                'town' => $townName
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


    public function getPwrDataAction()
    {
        $town = $this->getRequest()->getParam("town", "");
        $result = array();

        /* @var $collection ZolagoOs_Pwr_Model_Resource_Point_Collection */
        $collection = Mage::getModel("zospwr/point")->getCollection();
        $collection->addFieldToFilter("town", array("eq" => $town));
        $collection->addFieldToFilter("is_active", ZolagoOs_Pwr_Model_Point::STATUS_ACTIVE);
        $collection->addOrder("street", "ASC");

        $lockers = array();
        $streets = array();

        foreach ($collection as $locker) {
            /* @var $locker ZolagoOs_Pwr_Model_Point */
            $townName = (string)ucwords(strtolower($locker->getTown()));

            $lockers[] = array(
                "id" => $locker->getId(),
                "name" => $locker->getName(),
                'street' => (string)$locker->getStreet(),
                'building_number' => (string)$locker->getBuildingNumber(),
                "postcode" => (string)$locker->getPostcode(),
                'town' => $townName
            );

            $streets[$locker->getName()] = (string)$locker->getStreet(). " " . (string)$locker->getBuildingNumber();
        }
        if (!empty($lockers)) {
            $result["map_points"] = $lockers;
        }

        if (!empty($streets)) {
            $result["filters"] = $streets;
        }


        echo json_encode($lockers, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        exit;
    }
}