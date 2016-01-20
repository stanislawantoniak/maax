<?php

class Modago_Integrator_Adminhtml_Modagoapi_ConnectionController extends Mage_Adminhtml_Controller_Action {

	/**
	 * Test connection to Modago API
	 * Ajax make request from admin panel
	 */
	public function testAction() {
		/** @var Modago_Integrator_Helper_Api $helper */
		$helper = Mage::helper('modagointegrator/api');

		$login = $helper->getLogin();
		$apiKey = $helper->getApiKey();
		$password = $helper->getPassword();

		$data = $helper->testConnection($login, $password, $apiKey);
		if ($data['status']) {
			echo $helper->__("Connection established");
		} else {
			echo $helper->__("Connection not established (%s)", $data['msg']);
		}
	}

}