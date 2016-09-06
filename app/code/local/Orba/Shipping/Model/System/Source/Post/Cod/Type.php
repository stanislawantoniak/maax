<?php
class Orba_Shipping_Model_System_Source_Post_Cod_Type {


	public function toOptionArray() {
		Mage::getModel('orbashipping/post_client_wsdl'); // autoload
		$out = array();
		$_h = Mage::helper('orbashipping');
		$out[0] = $_h->__('Empty');
		$out[sposobPobraniaType::PRZEKAZ] = $_h->__('Bank transfer');
		$out[sposobPobraniaType::RACHUNEK_BANKOWY] = $_h->__('Bank account');
		return $out;
	}


}