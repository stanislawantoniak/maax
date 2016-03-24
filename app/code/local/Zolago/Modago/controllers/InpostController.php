<?php

class Zolago_Modago_InpostController extends Mage_Core_Controller_Front_Action
{
    public function getPopulateMapDataAction()
    {
        $town = $this->getRequest()->getParam("town", "");
        $result = array();

        /* @var $collection GH_Inpost_Model_Resource_Locker_Collection */
        $collection = Mage::getModel("ghinpost/locker")->getCollection();
        $collection->addFieldToFilter("town", array("eq" => $town));
        $collection->addFieldToFilter("is_active", GH_Inpost_Model_Locker::PAYMENT_AVAILABLE);
        $collection->addOrder("street", "ASC");

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

            $details = 
                    '<div class="row">'
                        . '<div class="col-sm-6">'
                            . '<div><b>' . $locker->getStreet() . ' ' . $locker->getBuildingNumber() . '</b></div>'
                            . "<div>" . $locker->getPostcode() . " " . $locker->getTown() . "</div>"
                            . "<div>(" . $locker->getLocationDescription() . ")</div>" 
                            . ( !empty($locker->getPaymentPointDescription()) ? "<div><span><i class='fa fa-credit-card fa-1x'></i> " . $locker->getPaymentPointDescription() . "</span></div>" : "")
                        . '</div>' 
                        .'<div class="col-sm-6">'
                            . '<a class="button button-third reverted large" data-select-shipping-method-trigger="1" data-carrier-pointid="' . $locker->getId() . '" data-carrier-pointcode="' . $locker->getName() . '" data-carrier-additional="' . $additionalHtml . '" href="">wybierz</a>'
                        . '</div>'
                    . '</div>'
            ;

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
                "point_details" => htmlentities($details)
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