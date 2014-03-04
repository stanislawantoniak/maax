<?php
class Zolago_Mapper_Model_Observer {
	public function zolagoMapperSaveAfter($observer) {
		Mage::log("Mapper save after");
	}
	public function catalogProductSaveAfter($observer) {
		Mage::log("Product save after");
	}
}