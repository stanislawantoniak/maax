<?php


class Zolago_Sales_Model_Quote_Address extends Mage_Sales_Model_Quote_Address {

    /**
     * Replace customer email with new email
     *
     * @param $newEmail
     * @param $customerId
     */
    public function replaceEmailInQuoteAddress($newEmail, $customerId)
    {
        if (empty($customerId)) {
            return;
        }
        $sameEmailCollection = $this->getCollection();
        $sameEmailCollection->addFieldToFilter("customer_id", $customerId);

        if ($sameEmailCollection->count()) {
            foreach ($sameEmailCollection as $quoteAddress) {
                $quoteAddress->setEmail($newEmail);
                $quoteAddress->save();
            }
        }
    }
}