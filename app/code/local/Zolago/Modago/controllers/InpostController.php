<?php

class Zolago_Modago_InpostController extends Mage_Core_Controller_Front_Action
{
    public function getPopulateMapDataAction()
    {
        $town = $this->getRequest()->getParam("town", "");
        $result = array();

        $collection = Mage::getModel("ghinpost/locker")->getCollection();
        if(!empty($town)){
            $collection->addFieldToFilter("town", array("eq" => $town));
        }



        $lockers = array();
        $streets = array();

        foreach ($collection as $locker) {
            /* @var $locker GH_Inpost_Model_Locker */
            

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
            $streets[$locker->getName()] = (string)$locker->getStreet(). " " . (string)$locker->getBuildingNumber();
        }
        if (!empty($lockers)) {
            $result["map_points"] = $lockers;
        }

        if (!empty($streets)) {
            $result["filters"] = $streets;
        }


        echo json_encode($result, JSON_HEX_APOS);
    }
}