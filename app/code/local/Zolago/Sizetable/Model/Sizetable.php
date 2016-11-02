<?php
class Zolago_Sizetable_Model_Sizetable extends Mage_Core_Model_Abstract{
	
	const ZOLAGO_SIZETABLE_ATTRIBUTE_CODE = 'custom_sizetable';


	protected function _construct() {
		$this->_init('zolagosizetable/sizetable');
	}

	/**
	 * @param mixed [] @data
	 * @throws Exception When array is empty and when cannot set values
	 * @return Zolago_Sizetable_Model_Sizetable
	 */
	public function updateModelData($data)
	{
		try {
			if (!empty($data)) {
				$vendor_id = Mage::getSingleton('udropship/session')->getVendor()->getVendorId();
				$this->setName($data['name']);
				$this->setVendorId($vendor_id);
				$defaultValue = $data['default_value'];
				if (!empty($defaultValue))
					$defaultValue = serialize($defaultValue);

				$this->setDefaultValue($defaultValue);
			} else {
				throw new Exception("Error Processing Request: Insufficient Data Provided.");
			}
		} catch (Exception $e) {
			Mage::logException($e);
		}
		return $this;
	}

	public function getScopes() {
		$scopes = $this->getResource()->getScopes($this->getSizetableId());
		$this->setSizetable($scopes);
		return $this;
	}
}