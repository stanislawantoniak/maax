<?php

class Gh_Wfirma_TestController extends Mage_Core_Controller_Front_Action {
	public function indexAction() {
		/** @var GH_Wfirma_Model_Client $client */
		$client = Mage::getModel('ghwfirma/client');

		error_reporting(E_ALL);
		ini_set("display_errors", 1);

		ini_set('xdebug.var_display_max_depth', 256);
		ini_set('xdebug.var_display_max_children', 1024);
		ini_set('xdebug.var_display_max_data', 5000);


		/** @var Zolago_Dropship_Model_Vendor $vendor */
		$vendor = Mage::getModel('udropship/vendor')->load(5); //matterhorn

		/** @var GH_Wfirma_Helper_Data $helper */
		$helper = Mage::helper('ghwfirma');

		/** @var Zolago_Payment_Model_Vendor_Invoice $model */
		$model = Mage::getModel("zolagopayment/vendor_invoice")->load(1);

		$helper->generateInvoice($model);



		return;
	}
}