<?php

class Zolago_Modago_InpostController extends Mage_Core_Controller_Front_Action
{
    public function getPopulateMapDataAction()
    {
        $town = $this->getRequest()->getParam("town", "");
        $result = array();

        $collection = Mage::getModel("ghinpost/locker")->getCollection();
        $collection->addFieldToFilter("town", array("eq" => $town));


        $lockers = array();        

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
        }
        echo json_encode($lockers, JSON_HEX_APOS);
    }
}