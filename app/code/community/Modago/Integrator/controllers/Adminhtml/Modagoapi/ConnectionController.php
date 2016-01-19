<?php

class Modago_Integrator_Adminhtml_Modagoapi_ConnectionController extends Mage_Adminhtml_Controller_Action {

	/**
	 * Test connection to Modago API
	 * Ajax make request from admin panel
	 */
	public function testAction() {
		/** @var Modago_Integrator_Helper_Api $helper */
		$helper = Mage::helper('modagointegrator/api');

		$req = $this->getRequest();
		$login = $req->getParam('login');
		$apiKey = $req->getParam('api_key');
		$password = $req->getParam('password');

		$data = $helper->testConnection($login, $password, $apiKey);
		if ($data['status']) {
			echo $helper->__("Success: connection established");
		} else {
			echo $helper->__("Fail: connection not established (%s)", $data['msg']);
		}
	}

}