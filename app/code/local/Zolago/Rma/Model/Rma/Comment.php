<?php

class Zolago_Rma_Model_Rma_Comment extends ZolagoOs_Rma_Model_Rma_Comment
{
	
	const CUSTOMER	= "customer";
	const VENDOR	= "vendor";
	const OPERATOR	= "operator";
	const SYSTEM	= "system";
	
	protected static $_authors = array(
		self::CUSTOMER	=> array(),
		self::VENDOR	=> array(),
		self::OPERATOR	=> array()
	);
	
	/**
	 * @return ZolagoOs_OmniChannel_Model_Vendor
	 */
	public function getVednor() {
		$vendorId = $this->getVendorId();
		if(is_null($vendorId)){
			return Mage::getModel("udropship/vendor");
		}
		
		if(!isset(self::$_authors[self::VENDOR][$vendorId])){
			self::$_authors[self::VENDOR][$vendorId] = Mage::getModel("udropship/vendor")->load($vendorId);
		}
		return self::$_authors[self::VENDOR][$vendorId];
	}
	
	/**
	 * @return Zolago_Operator_Model_Operator
	 */
	public function getOperator() {
		$operatorId = $this->getOperatorId();
		if(is_null($operatorId)){
			return Mage::getModel("zolagooperator/operator");
		}
		
		if(!isset(self::$_authors[self::OPERATOR][$operatorId])){
			self::$_authors[self::OPERATOR][$operatorId] = Mage::getModel("zolagooperator/operator")->load($operatorId);
		}
		return self::$_authors[self::OPERATOR][$operatorId];
	}
	
	/**
	 * @return Mage_Customer_Model_Customer
	 */
	public function getCustomer() {
		$customerId = $this->getCustomerId();
		if(is_null($customerId)){
			return Mage::getModel("customer/customer");
		}
		
		if(!isset(self::$_authors[self::CUSTOMER][$customerId])){
			self::$_authors[self::CUSTOMER][$customerId] = Mage::getModel("customer/customer")->load($customerId);
		}
		return self::$_authors[self::CUSTOMER][$customerId];
	}
	
	/**
	 * @return Mage_Core_Model_Abstract | null
	 */
	public function getAuthorObject() {
		if($this->getCustomer()->getId()){
			return $this->getCustomer();
		}elseif($this->getOperator()->getId()){
			return $this->getOperator();
		}elseif($this->getVednor()->getId()){
			return $this->getVednor();
		}
		return null;
	}
	
	/**
	 * @return string
	 */
	public function getAuthorName($fromObjData=true) {
		if($fromObjData){
			return parent::getAuthorName();
		}
		if($this->getCustomer()->getId()){
			return $this->getCustomer()->getName();
		}elseif($this->getOperator()->getId()){
			return $this->getOperator()->getFullname();
		}elseif($this->getVednor()->getId()){
			return $this->getVednor()->getVendorName();
		}
		return Mage::helper('zolagorma')->__("System");
	}
	
	/**
	 * @return string
	 */
	public function getAuthorType() {
		if($this->getCustomerId()){
			return self::CUSTOMER;
		}elseif($this->getOperatorId()){
			return self::OPERATOR;
		}elseif($this->getVendorId()){
			return self::VENDOR;
		}
		return self::SYSTEM;
	}
	
	/**
	 * @return string
	 */
	public function getAuthorTypeText() {
		switch ($this->getAuthorType()) {
			case self::CUSTOMER:
				return Mage::helper('zolagorma')->__("Customer");
			break;
			case self::VENDOR:
				return Mage::helper('zolagorma')->__("Vendor");
			break;
			case self::OPERATOR:
				return Mage::helper('zolagorma')->__("Operator");
			break;
		}
		return Mage::helper('zolagorma')->__("System");
	}
	
	/**
	 * @return string
	 */
	public function getRmaStatusName() {
		return Mage::helper('zolagorma')->getRmaStatusName($this->getRmaStatus());
	}
	
	/**
	 * @return null
	 */
	public function _beforeSave() {
		if($this->isObjectNew()){
			$this->setRmaStatus($this->getRma()->getRmaStatus());
			// Save author name by as text
			if($this->getAuthorName(false) && !parent::getAuthorName()){
				$this->setAuthorName($this->getAuthorName(false));
			}
			$this->setComment(trim($this->getComment()));
		}
		return parent::_beforeSave();
	}
}
