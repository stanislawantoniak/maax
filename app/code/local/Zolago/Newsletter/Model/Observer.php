<?php
class Zolago_Newsletter_Model_Observer extends Mage_Newsletter_Model_Observer
{
	public function subscribeCustomer($observer)
	{
		$customer = $observer->getEvent()->getCustomer();
		if (($customer instanceof Mage_Customer_Model_Customer)) {

			if($customer->dataHasChangedFor("email")){
				$customer->setIsEmailHasChanged(true);
			}elseif(!is_null($customer->getIsSubscribed())
			        && $customer->getIsSubscribed() != Mage::getModel("zolagonewsletter/subscriber")->getCustomerIsSubscribed($customer)) {
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
		$subscriber = Mage::getModel('zolagonewsletter/subscriber')
			->loadByEmail($observer->getEvent()->getCustomer()->getEmail());
		if($subscriber->getId()) {
			$subscriber->delete();
		}
		return $this;
	}
}
