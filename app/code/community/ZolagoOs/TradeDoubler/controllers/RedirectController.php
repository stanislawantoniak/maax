<?php

class ZolagoOs_TradeDoubler_RedirectController extends Mage_Core_Controller_Front_Action {
	
	public function indexAction() {
		// If a "tduid" parameter has been passed, save it in a cookie and the current session
		$tduid = $this->getRequest()->getParam("tduid");
		$url   = $this->getRequest()->getParam("url");
		$redirectUrl = Mage::getBaseUrl();

		if (!empty($tduid)) {
			$baseUrl = Mage::getBaseUrl (Mage_Core_Model_Store::URL_TYPE_WEB); // http://www.example.pl/
			$host = parse_url($baseUrl, PHP_URL_HOST); // www.example.pl
			$domain = str_replace('www.', '', $host); // example.pl

			setcookie("TRADEDOUBLER", $tduid, (time() + 3600 * 24 * 365), "/", '.' . $domain);
		}

		// If a redirect URL has been set, redirect to that URL
		if (!empty($url)) {
			$redirectUrl = $url;
		}
		
		$this->getResponse()->setRedirect($redirectUrl);
	}
}