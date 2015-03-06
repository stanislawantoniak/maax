<?php

class Zolago_Sales_Helper_Attach extends Mage_Core_Helper_Abstract
{

	protected $attachOrdersInited = false;
	protected $attachOrdersIds;
	protected $attachAddressesIds;
	protected $attachCustomerId;
	protected $attachCustomer;

	public function attachOrders($token)
	{
		$this->_initAttachVars();
		$_helper = $this->_getSalesHelper();

		//check token
		/** @var Zolago_Customer_Model_Attachtoken $model */
		$model = Mage::getModel('zolagocustomer/attachtoken');
		$collection = $model->getCollection();
		$collection->setFilterToken($token);

		if (!count($collection)) {
			// no token
			return $_helper->__("Confirmation token does not exist");
		}

		$searchModel = $collection->getFirstItem();
		$this->attachCustomerId = $searchModel->getCustomerId();
		$searchModel->delete();

		$email = $this->_getAttachCustomer()->getEmail();

		try {
			//using iterator to prevent out of memory errors with large number of orders to attach
			/** @var Mage_Core_Model_Resource_Iterator $iterator */
			$iterator = Mage::getSingleton('core/resource_iterator');

			//attach orders
			$orders = $_helper->getGuestOrders($email, true);
			if($orders->count()) {
				$iterator->walk(
					$orders->getSelect(),
					array(array($this, '_attachOrderCallback'))
				);

				//attach pos
				/** @var Zolago_Po_Model_Resource_Po_Collection $poCollection */
				$pos = Mage::getResourceModel("zolagopo/po_collection");
				$pos->addFieldToFilter('order_id', array('in' => $this->attachOrdersIds));
				$iterator->walk(
					$pos->getSelect(),
					array(array($this, '_attachPoCallback'))
				);

				//attach addresses
				/** @var Mage_Sales_Model_Resource_Order_Address_Collection $addresses */
				$addresses = Mage::getResourceModel("sales/order_address_collection");
				$addresses->addFieldToFilter('entity_id', array('in' => $this->attachAddressesIds));
				$iterator->walk(
					$addresses->getSelect(),
					array(array($this, '_attachAddressCallback'))
				);

				//todo: attach rmas

				$result = true;
			} else {
				$result = $_helper->__("There are no more orders that can be attached to your account");
			}
		} catch (Exception $e) {
			$result = $_helper->__("There was an error while attaching your orders!");
		}
		return $result;
	}

	public function _attachOrderCallback($args)
	{
		/** @var Zolago_Sales_Model_Order $order */
		if ($this->_attachVarsInited()) {
			$order = Mage::getModel("sales/order");
			$order->setData($args['row']);
			$order->assignToCustomer($this->attachCustomerId, true);
			$this->attachAddressesIds[] = $order->getShippingAddressId();
			$this->attachAddressesIds[] = $order->getBillingAddressId();
			$this->attachOrdersIds[] = $order->getId();
		}
	}

	public function _attachPoCallback($args)
	{
		if ($this->_attachVarsInited()) {
			/** @var Zolago_Po_Model_Po $po */
			$po = Mage::getModel("zolagopo/po");
			$po->setData($args['row']);
			$po->setCustomerId($this->attachCustomerId)->save();
		}
	}

	public function _attachAddressCallback($args)
	{
		if ($this->_attachVarsInited()) {
			/** @var Mage_Sales_Model_Order_Address $address */
			$address = Mage::getModel("sales/order_address");
			$address->setData($args['row']);
			$address->setCustomerId($this->attachCustomerId)->save();

			//check if user has already this address in his addressbook
			$addressHash = $this->_getAddressHash($address->getData());
			if (!in_array($addressHash, $this->_getCustomerAddressesHashes())) {
				//if not then put it there
				/** @var Mage_Customer_Model_Address $newAddress */
				$newAddress = Mage::getModel("customer/address");
				$newAddress->setCustomerId($this->attachCustomerId)
					->setFirstname($address->getFirstname())
					->setMiddleName($address->getMiddlename())
					->setLastname($address->getLastname())
					->setCountryId($address->getCountryId())
					->setRegionId(($address->getRegionId()))
					->setPostcode($address->getPostcode())
					->setCity($address->getCity())
					->setTelephone($address->getTelephone())
					->setFax($address->getFax())
					->setCompany($address->getCompany())
					->setStreet($address->getStreet())
					->setIsDefaultBilling('0')
					->setIsDefaultShipping('0')
					->setSaveInAddressBook('1')
					->save();
				$this->attachCustomerAddressesHashes[] = $addressHash;
			}
		}
	}

	protected $attachCustomerAddressesHashes = false;
	protected function _getCustomerAddressesHashes()
	{
		if (!$this->attachCustomerAddressesHashes) {
			/** @var Mage_Customer_Model_Customer $customer */
			$customer = $this->_getAttachCustomer();
			$addresses = $customer->getAddresses();

			$out = array();
			foreach ($addresses as $address) {
				$out[] = $this->_getAddressHash($address->getData());
			}
			$this->attachCustomerAddressesHashes = $out;
		}
		return $this->attachCustomerAddressesHashes;
	}

	/**
	 * $data is getData() from address object
	 * @param $data
	 * @return string
	 */
	protected function _getAddressHash($data)
	{
		/** @var Zolago_Common_Helper_Data $helper */
		$helper = Mage::helper("zolagocommon");

		$hash = '';
		$takenToHash = $this->_getFieldsToHash();
		$data['company'] = isset($data['company']) ? $data['company'] : '';
		$data['need_invoice'] = !isset($data['need_invoice']) ? 0 : $data['need_invoice'] ? 1 : 0;
		ksort($data);
		foreach ($data as $field => $value) {
			if (in_array($field, $takenToHash)) {

				$hash .= $field .
					str_replace( //remove whitespaces and some chars used commonly in addresses
						array(".", ",", "/", "'", "\"", "\n", "\r", " ", "-", "_"), "",
						strtolower( //convert to lowercase
							$helper->str2Url($value)
						)
					);
			}
		}
		return $hash;
	}

	/**
	 * @return array
	 */
	protected function _getFieldsToHash()
	{
		return array('firstname', 'lastname', 'company', 'city', 'postcode', 'country_id', 'telephone', 'need_invoice', 'street');
	}

	/**
	 * @return Zolago_Customer_Model_Customer
	 */
	protected function _getAttachCustomer()
	{
		if (is_null($this->attachCustomer)) {
			$this->attachCustomer = Mage::getModel("customer/customer")->load($this->attachCustomerId);
		}
		return $this->attachCustomer;
	}

	protected function _initAttachVars()
	{
		if (!$this->_attachVarsInited()) {
			$this->attachAddressesIds = array();
			$this->attachOrdersIds = array();
			$this->attachCustomerId = 0;
			$this->attachOrdersInited = true;
		}
	}

	protected function _attachVarsInited()
	{
		return $this->attachOrdersInited && $this->attachCustomerId;
	}

	/**
	 * @return Mage_Customer_Model_Session
	 */
	protected function _getCustomerSession() {
		return Mage::getSingleton("customer/session");
	}

	/**
	 * @return Zolago_Sales_Helper_Data
	 */
	protected function _getSalesHelper() {
		return Mage::helper("zolagosales");
	}
}