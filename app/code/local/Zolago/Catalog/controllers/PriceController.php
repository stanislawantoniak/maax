<?php
class Zolago_Catalog_PriceController extends Mage_Core_Controller_Front_Action
{
	
	public function indexAction() {
		echo "Works";
	}

	public function pricetypeAction() {
		Zolago_Catalog_Model_Observer::processPriceTypeQueue();
		echo "End Pricetype";
	}
	
	public function configurableAction() {
		Zolago_Catalog_Model_Observer::processConfigurableQueue();
		echo "End Configurable";
	}

}



