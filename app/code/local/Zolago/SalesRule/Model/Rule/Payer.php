<?php
class Zolago_SalesRule_Model_Rule_Payer {
	
	const PAYER_VENDOR = 0;
	const PAYER_GALLERY = 1;
	
	/**
	 * @return array
	 */
	public function toOptionHash() {
		return array(
			self::PAYER_VENDOR => Mage::helper("zolagosalesrule")->__("Vednor"),
			self::PAYER_GALLERY => Mage::helper("zolagosalesrule")->__("Gallery")
		);
	}
	
	/**
	 * @return array 
	 */
	public function toOptionArray() {
		return array(
			array(
				"value" => self::PAYER_VENDOR,
				"label" => Mage::helper("zolagosalesrule")->__("Vednor")
			),
			array(
				"value" => self::PAYER_GALLERY,
				"label" => Mage::helper("zolagosalesrule")->__("Gallery")
			)
		);
	}
}
