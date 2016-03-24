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

            $additionalHtml = htmlentities(implode("<br />", $additional));

            $details = array(
                "<b>".$locker->getStreet() . " " . $locker->getBuildingNumber()."</b>",
                $locker->getPostcode() . " " . $locker->getTown(),
                "(" . $locker->getLocationDescription() . ")",
                (!empty($locker->getPaymentPointDescription()) ? "<span><i class='fa fa-credit-card fa-1x'></i> ".$locker->getPaymentPointDescription()."</span>" : ""),
                '<div><a class="button button-third small" data-select-shipping-method-trigger="1" data-carrier-pointid="'.$locker->getId().'" data-carrier-pointcode="'.$locker->getName().'" data-carrier-additional="'.$additionalHtml.'" href="">wybierz ten adres</a></div>'
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
                "additional" => $additionalHtml,
                "point_details" => htmlentities(implode("<br />", $details))
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