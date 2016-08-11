<?php
class Zolago_SalesRule_Model_Promotion_Type {
	
	const PROMOTION_NONE = 0;
	const PROMOTION_SUBSCRIBERS = 1;
	
	/**
	 * @return array
	 */
	public function toOptionHash() {
		return array(
			self::PROMOTION_NONE => Mage::helper("zolagosalesrule")->__("Standard"),
			self::PROMOTION_SUBSCRIBERS => Mage::helper("zolagosalesrule")->__("For subscribers")
		);
	}
	
	/**
	 * @return array 
	 */
	public function toOptionArray() {
		return array(
			array(
				"value" => self::PROMOTION_NONE,
				"label" => Mage::helper("zolagosalesrule")->__("Standard")
			),
			array(
				"value" => self::PROMOTION_SUBSCRIBERS,
				"label" => Mage::helper("zolagosalesrule")->__("For subscribers")
			)
		);
	}
}
