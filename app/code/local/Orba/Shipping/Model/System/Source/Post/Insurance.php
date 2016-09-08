<?php
class Orba_Shipping_Model_System_Source_Post_Insurance {


	public function toOptionArray() {
		Mage::getModel('orbashipping/post_client_wsdl'); // autoload
		$out = array();
		$_h = Mage::helper('orbashipping');
		$out[0] = $_h->__('None');
		$out[kwotaUbezpieczeniaType::KWOTA_5000] = sprintf("%s PLN",kwotaUbezpieczeniaType::KWOTA_5000/100);
		$out[kwotaUbezpieczeniaType::KWOTA_10000] = sprintf("%s PLN",kwotaUbezpieczeniaType::KWOTA_10000/100);
		$out[kwotaUbezpieczeniaType::KWOTA_20000] = sprintf("%s PLN",kwotaUbezpieczeniaType::KWOTA_20000/100);
		$out[kwotaUbezpieczeniaType::KWOTA_50000] = sprintf("%s PLN",kwotaUbezpieczeniaType::KWOTA_50000/100);
		$out[-1] = $_h->__('Order value');
		return $out;
	}


}