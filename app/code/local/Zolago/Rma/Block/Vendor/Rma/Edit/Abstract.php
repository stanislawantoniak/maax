<?php
/**
 * @method Zolago_Rma_Block_Vendor_Rma_Edit getParentBlock()
 */
abstract class Zolago_Rma_Block_Vendor_Rma_Edit_Abstract
	extends Mage_Core_Block_Template
{
	/**
	 * @return Zolago_Po_Model_Po
	 */
	public function getPo() {
		return $this->getParentBlock()->getPo();
	}
	
	/**
	 * @param string $action
	 * @param array|null $params
	 * @return string
	 */
	public function getPoUrl($action, $params=array()) {
		return $this->getParentBlock()->getPoUrl($action, $params);
	}
	
	/**
	 * @return ZolagoOs_OmniChannel_Model_Vendor
	 */
	public function getVendor() {
		return $this->getParentBlock()->getVendor();
	}
	
	/**
	 * @return Zolago_Rma_Model_Rma
	 */
	public function getRma() {
		return $this->getParentBlock()->getRma();
	}
	
	/**
	 * @param string $action
	 * @param array|null $params
	 * @return string
	 */
	public function getRmaUrl($action, $params=array()) {
		return $this->getParentBlock()->getRmaUrl($action, $params);
	}
	
}
