<?php
class Zolago_Po_Model_Po_Alert
{
	const ALERT_SAME_EMAIL_PO = 1;
   
	
	public static function getAllOptions() {
		return array(
			self::ALERT_SAME_EMAIL_PO => Mage::helper('zolagopo')->__("Another order from same customer")
		);
	}
	
	public static function getAlertText($int) {
		switch ($int) {
			case self::ALERT_SAME_EMAIL_PO:
				return "There is another order from the same customer. Click the %s to see the customer order.";
			break;
		}
		return "";
	}
}
