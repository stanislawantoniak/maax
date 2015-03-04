<?php

class Zolago_Sales_Model_Order_Address extends Mage_Sales_Model_Order_Address {


    /**
     * Replace customer email with new email
     *
     * @param $newEmail
     * @param $customerId
     */
    public function replaceEmailInOrderAddress($newEmail, $customerId)
    {
        if (empty($customerId)) {
            return;
        }
        $sameEmailCollection = $this->getCollection();
        $sameEmailCollection->addFieldToFilter("customer_id", $customerId);

        if ($sameEmailCollection->count()) {
            foreach ($sameEmailCollection as $orderAddress) {
                $orderAddress->setEmail($newEmail);
                $orderAddress->save();
            }
        }
    }
}