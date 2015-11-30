<?php
class Zolago_Sales_Helper_Data extends Mage_Sales_Helper_Data {

    public function getOpenedOrders() {	
        $openedOrders = Mage::getResourceModel('sales/order_collection')
            ->addFieldToSelect('*')
            ->addFieldToFilter('customer_id', Mage::getSingleton('customer/session')->getCustomer()->getId())
            ->addFieldToFilter('state', array('in' => Mage::getSingleton('sales/order_config')->getOpenOrdersStates()));
        return $openedOrders;
    }

	/**
	 * @param string|bool $email
	 * @param bool $getAllData
	 * @return Mage_Sales_Model_Resource_Order_Collection
	 */
	public function getGuestOrders($email=false,$getAllData=false) {
		$field = $getAllData ? "*" : "entity_id";
		$customer = $this->getCustomerSession()->getCustomer();
		$email = $email ? $email : $customer->getEmail();
		$guestOrders = Mage::getResourceModel('sales/order_collection')
			->addFieldToSelect($field)
			->addFieldToFilter('store_id',Mage::app()->getStore()->getId())
			->addFieldToFilter('customer_email', $email)
			->addFieldToFilter('customer_id',array(array('neq' => $customer->getId()), array('null'=>true)));

		return $guestOrders;
	}

	public function getCustomerSession() {
		return  Mage::getSingleton('customer/session');
	}
} 