<?php

class Zolago_Modago_Block_Checkout_Cart_Sidebar_Shipping_Map
    extends Zolago_Modago_Block_Checkout_Cart_Sidebar_Shipping
{

    public function getPopulateData()
    {
        $lockers = array();
        $collection = Mage::getModel("ghinpost/locker")->getCollection();
        $collection->setPageSize(10);
        foreach ($collection as $locker) {
            $lockers[$locker->getName()] = $this->getLockerRender($locker);
        }
        return $lockers;
    }
}