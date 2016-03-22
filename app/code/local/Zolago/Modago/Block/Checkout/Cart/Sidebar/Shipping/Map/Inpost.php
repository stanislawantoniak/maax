<?php

/**
 * Class Zolago_Modago_Block_Checkout_Cart_Sidebar_Shipping_Map
 */
class Zolago_Modago_Block_Checkout_Cart_Sidebar_Shipping_Map_Inpost
    extends Zolago_Modago_Block_Checkout_Cart_Sidebar_Shipping
{

    public function getPopulateData()
    {
        $lockers = array();
        $collection = Mage::getModel("ghinpost/locker")->getCollection();
        $collection->setPageSize(10);
        foreach ($collection as $locker) {
            /* @var $locker GH_Inpost_Model_Locker  */
            $lockers[$locker->getName()] = $this->getLockerRender($locker);
        }
        return $lockers;
    }
    
    public static function getPopulateMapData() {
        $result = "";
        $lockers = array();
        $collection = Mage::getModel("ghinpost/locker")->getCollection();
        //$collection->setPageSize(10);
        foreach ($collection as $locker) {
            /* @var $locker GH_Inpost_Model_Locker  */
            $lockers[] = array(
                "id" => $locker->getId(),
                "name" => $locker->getName(),
                'street' => $locker->getStreet(),
                "longitude" => $locker->getLongitude(),
                "latitude" => $locker->getLatitude()
            );
        }
        if (!empty($lockers)) {
            $result = json_encode($lockers, JSON_HEX_APOS);
        }

        return $result;
    }

}