<?php
class Zolago_Customer_Helper_Ghost extends Mage_Core_Helper_Abstract {
	
    const EMAIL = "ghost@zolago.pl";
	/**
	 * @param type $websiteId
	 * @return Mage_Customer_Model_Customer
	 */
	public function getCustomer($websiteId=null) {
		if($websiteId===null){
			$websiteId = Mage::app()->getWebsite()->getId();
		}
		$customer = Mage::getModel("customer/customer");
		/* @var $customer Mage_Customer_Model_Customer */
		$customer->setWebsiteId($websiteId);
		$customer->loadByEmail(self::EMAIL);
		if(!$customer->getId()){
			$customer->setEmail(self::EMAIL);
			$customer->setFirstname("Ghost");
			$customer->setLastname("Ghost");
			$customer->save();
		}
		return $customer;
	}
}