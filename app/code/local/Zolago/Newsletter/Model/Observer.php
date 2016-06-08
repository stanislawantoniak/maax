<?php

/**
 * Class Zolago_Newsletter_Model_Observer
 */
class Zolago_Newsletter_Model_Observer extends Mage_Newsletter_Model_Observer
{
    public function subscribeCustomer($observer)
    {
        if (!Mage::helper("zolagonewsletter")->isModuleActive())
            return parent::subscribeCustomer($observer);

        /** @var Zolago_Customer_Model_Customer $customer */
        $customer = $observer->getEvent()->getCustomer();
        if (($customer instanceof Mage_Customer_Model_Customer)) {

            if ($customer->dataHasChangedFor("email") && !is_null($customer->getOrigData("email"))) {
                $customer->setIsEmailHasChanged(true);
            } elseif (!is_null($customer->getIsSubscribed())
                && $customer->getIsSubscribed() != Mage::getModel("zolagonewsletter/subscriber")->getCustomerIsSubscribed($customer)
            ) {
                //			}elseif($customer->getIsSubscribed() !== Mage::getModel("zolagonewsletter/subscriber")->getCustomerIsSubscribed($customer)) {
                $customer->setIsSubscribedHasChanged(true);
            }

            Mage::getModel('zolagonewsletter/subscriber')->subscribeCustomer($customer);
        }
        return $this;
    }

    /**
     * Customer delete handler
     *
     * @param Varien_Object $observer
     * @return Mage_Newsletter_Model_Observer
     */
    public function customerDeleted($observer)
    {
        if (!Mage::helper("zolagonewsletter")->isModuleActive())
            return parent::customerDeleted($observer);

        $subscriber = Mage::getModel('zolagonewsletter/subscriber')
            ->loadByEmail($observer->getEvent()->getCustomer()->getEmail());
        if ($subscriber->getId()) {
            $subscriber->delete();
        }
        return $this;
    }
}
