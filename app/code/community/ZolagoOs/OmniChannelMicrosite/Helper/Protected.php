<?php

class ZolagoOs_OmniChannelMicrosite_Helper_Protected
{
	protected $_gcvCycleCheck = false;
	protected $_currentVendorFromProduct = NULL;
	protected $_currentVendor = NULL;
	protected $_origBaseUrl = NULL;
	protected $_parsedBaseUrl = NULL;
	protected $_vendorBaseUrl = array();

	final public static function validateLicense() {
	    return true;
		try {
			ZolagoOs_OmniChannel_Helper_Protected::validateLicense("ZolagoOs_OmniChannelMicrosite");
		} catch (Exception $e) {
			Mage::logException($e);
			return false;
		}
		return true;
	}

	public function isCurrentVendorFromProduct() {
		return $this->_currentVendorFromProduct;
	}

	public function resetCurrentVendor() {
		$this->_currentVendorFromProduct = null;
		$this->_currentVendor = null;
		return $this;
	}

	public function getCurrentVendor() {
		if (!self::validateLicense()) {
			return false;
		}

		if (is_null($this->_currentVendor) && !$this->_gcvCycleCheck) {
			$this->_gcvCycleCheck = true;
			if (Mage::app()->getStore()->isAdmin()) {
				if ($vendor = $this->getAdminhtmlVendor()) {
					$this->_currentVendor = $vendor;
				} else {
					$this->_currentVendor = false;
				}
			} else {
				if ($vendor = $this->getFrontendVendor()) {
					$this->_currentVendor = $vendor;
				} else {
					if ($product = Mage::registry("current_product")) {
						$this->_currentVendor = Mage::helper("udropship")->getVendor($product);
						if (!$this->_currentVendor->getId()) {
							$this->_currentVendor = false;
						} else {
							$this->_currentVendorFromProduct = true;
						}
					} else {
						if (Mage::app()->getRequest()->getRouteName()) {
							$this->_currentVendor = false;
						}
					}
				}
			}
			$this->_gcvCycleCheck = false;
		}

		return $this->_currentVendor;
	}

	public function getUrlFrontendVendor($url) {
		return $this->_getFrontendVendor($url);
	}

	public function getFrontendVendor() {
		$this->_origBaseUrl = Mage::getStoreConfig("web/unsecure/base_link_url");
		$url = parse_url($this->_origBaseUrl);
		$this->_parsedBaseUrl = $url;
		return $this->_getFrontendVendor();
	}

	protected function _getFrontendVendor($useUrl = false) {
		$url = null;
		if (!$useUrl) {
			$this->_origBaseUrl = Mage::getStoreConfig("web/unsecure/base_link_url");
			$url = parse_url($this->_origBaseUrl);
			$this->_parsedBaseUrl = $url;
			$httpHost = $_SERVER["HTTP_HOST"];
		} else {
			$url = parse_url($useUrl);
			$httpHost = $url["host"];
		}

		if (empty($httpHost)) {
			return false;
		}

		$level = Mage::getStoreConfig("udropship/microsite/subdomain_level");
		if ($level == 1) {
			$vUrlKey = $this->_getVendorKeyFromRequest($useUrl);
		} else {
			$host = $httpHost;
			$hostArr = explode(".", trim($host, "."));
			$i = sizeof($hostArr) - $level;			
			$vUrlKey = isset($hostArr[$i])? $hostArr[$i]:null;
		}

		if (empty($level) || empty($vUrlKey)) {
			return false;
		}

		$rHlp = Mage::getResourceSingleton("udropship/helper");
		$rCon = $rHlp->getReadConnection();
		$dbRow = $rHlp->loadDbColumns(Mage::getModel("udropship/vendor"), true, array("vendor_id", "url_key", "status"), $rCon->quoteInto("url_key=?", $vUrlKey));
		if (empty($dbRow)) {
			return false;
		}

		$vendor = Mage::getModel("udropship/vendor")->load($vUrlKey, "url_key");
		if (!$vendor->getId()) {
			return false;
		}

		if ($vendor->getStatus() != "A") {
			Mage::getSingleton("core/session", array("name" => "frontend"))->start("frontend");
			$session = Mage::getSingleton("udropship/session");
			if ($session->getId() != $vendor->getId()) {
				return false;
			}

		}

		if (!$this->isAllowedAction("microsite", $vendor)) {
			return false;
		}

		if (!$useUrl) {
			if (1 < $level) {
				if ($this->updateStoreBaseUrl()) {
					$baseUrl = $url["scheme"] . "://" . $host . (isset($url["path"]) ? $url["path"] : "/");
					Mage::app()->getStore()->setConfig("web/unsecure/base_link_url", $baseUrl);
				}
			} else {
				$this->_removeVendorKeyFromRequest();
			}
		}

		return $vendor;
	}

	public function checkPermission($action, $vendor = null) {
		$permData = $this->_checkPermission($action, $vendor);
		if (!$permData["allowed"]) {
			if ($permData["redirect"]) {
				header("Location: " . $permData["redirect"]);
				return NULL;
			}

			Mage::throwException($this->__("\"%s\" action is not allowed", $action));
		}

	}

	public function isAllowedAction($action, $vendor = null) {
		return $this->_checkPermission($action, $vendor, true);
	}

	protected function _checkPermission($action, $vendor = null, $asBool = false) {
		$result = true;
		if (null === $vendor) {
			$vendor = $this->getCurrentVendor();
		}

		static $transport;
		if ($transport === null) {
			$transport = new Varien_Object();
		}

		$transport->setAllowed($result);
		Mage::dispatchEvent("umicrosite_check_permission", array("action" => $action, "vendor" => $vendor, "transport" => $transport));
		return $asBool ? $transport->getAllowed() : $transport->getData();
	}

	public function updateStoreBaseUrl() {
		if (!self::validateLicense()) {
			return false;
		}

		return Mage::getStoreConfig("udropship/microsite/update_store_base_url");
	}

	public function getVendorBaseUrl($vendor = null) {
		if (!self::validateLicense()) {
			return false;
		}

		$level = Mage::getStoreConfig("udropship/microsite/subdomain_level");
		if (is_null($vendor) || $vendor === true) {
			$vendor = $this->getCurrentVendor();
		} else {
			$vendor = Mage::helper("udropship")->getVendor($vendor);
		}

		if (!$level || !$vendor || !$vendor->getId() || !$vendor->getUrlKey()) {
			return $this->_origBaseUrl;
		}

		$vId = $vendor->getId();
		if (!isset($this->_vendorBaseUrl[$vId])) {
			$store = Mage::app()->getStore();
			if ($this->updateStoreBaseUrl() && $this->getFrontendVendor() && $this->getFrontendVendor()->getId() == $vendor->getId()) {
				$baseUrl = $store->getBaseUrl();
			} else {
				if (1 == $level) {
					$store->useVendorUrl($vId);
					$baseUrl = $store->getBaseUrl();
					$store->resetUseVendorUrl();
				} else {
					$url = $this->_parsedBaseUrl;
					$hostArr = explode(".", trim($url["host"], "."));
					$l = sizeof($hostArr);
					if (0 <= $l - $level) {
						$hostArr[$l - $level] = $vendor->getUrlKey();
					} else {
						array_unshift($hostArr, $vendor->getUrlKey());
					}

					$baseUrl = $url["scheme"] . "://" . join(".", $hostArr) . (isset($url["path"]) ? $url["path"] : "/");
				}

			}

			$this->_vendorBaseUrl[$vId] = $baseUrl;
		}

		return $this->_vendorBaseUrl[$vId];
	}

	protected function _getVendorKeyFromRequest($url = false) {
		$request = $url ? new Mage_Core_Controller_Request_Http($url) : Mage::app()->getRequest();
		$pathInfo = $request->getPathInfo();
		$pathParts = explode("/", ltrim($pathInfo, "/"), 2);
		$parsedRequest = $this->_parseRequest($request);
		return $parsedRequest[0];
	}

	protected function _parseRequest($request = false) {
		$request = $request ? $request : Mage::app()->getRequest();
		$requestUri = $request->getRequestUri();
		if (null === $requestUri) {
			$parsedRequest = array(null, null, null);
		} else {
			$pos = strpos($requestUri, "?");
			if ($pos) {
				$requestUri = substr($requestUri, 0, $pos);
			}

			$baseUrl = $request->getBaseUrl();
			$pathInfo = substr($requestUri, strlen($baseUrl));
			if (null !== $baseUrl && false === $pathInfo) {
				$pathInfo = "";
			} else {
				if (null === $baseUrl) {
					$pathInfo = $requestUri;
				}
			}

			if ($baseUrl && strlen($baseUrl)) {
				$baseUrl = substr($requestUri, 0, strlen($baseUrl));
			}

			$pathParts = explode("/", ltrim($pathInfo, "/"), 2);
			$vUrlKey = $pathParts[0];
			$pathInfo = "/" . (isset($pathParts[1]) ? $pathParts[1] : "");
			$parsedRequest = array($vUrlKey, $pathInfo, rtrim($baseUrl, "/") . $pathInfo . ($pos !== false ? substr($requestUri, $pos) : ""));
		}

		return $parsedRequest;
	}

	protected function _removeVendorKeyFromRequest() {
		$parsedRequest = $this->_parseRequest();
		Mage::app()->getRequest()->setRequestUri($parsedRequest[2]);
		Mage::app()->getRequest()->setActionName(null);
		Mage::app()->getRequest()->setPathInfo();
	}

	public function withOrigBaseUrl($url, $prefix = "") {
		if (!self::validateLicense()) {
			return false;
		}

		$level = Mage::getStoreConfig("udropship/microsite/subdomain_level");
		if (!$level) {
			return $url;
		}

		$p = parse_url($url);
		$host = join(".", array_slice(explode(".", trim($p["host"], ".")), 1 - $level));
		return $p["scheme"] . "://" . $prefix . $host . $p["path"] . (!empty($p["query"]) ? "?" . $p["query"] : "") . (!empty($p["fragment"]) ? "?" . $p["fragment"] : "");
	}

	public function getVendorUrl($vendor, $origUrl = null) {
		if (!self::validateLicense()) {
			return false;
		}

		if ($vendor === true) {
			$vendor = $this->getCurrentVendor();
			if (!$vendor) {
				return $origUrl;
			}
		} else {
			$vendor = Mage::helper("udropship")->getVendor($vendor);
		}

		$vendorBaseUrl = $this->getVendorBaseUrl($vendor);
		if (is_null($origUrl)) {
			return $vendorBaseUrl;
		}

		if ($origUrl instanceof Mage_Catalog_Model_Product) {
			$origUrl = $origUrl->getProductUrl();
		}

		if ($this->updateStoreBaseUrl() && ($curVendor = $this->getCurrentVendor())) {
			if ($curVendor->getId() == $vendor->getId()) {
				return $origUrl;
			}

			$origBaseUrl = $this->getVendorBaseUrl($curVendor);
		} else {
			$origBaseUrl = $this->_origBaseUrl;
		}

		return str_replace($origBaseUrl, $vendorBaseUrl, $origUrl);
	}

	public function getAdminhtmlVendor() {
		if (!self::validateLicense()) {
			return false;
		}

		Mage::getSingleton("core/session", array("name" => "adminhtml"))->start("adminhtml");
		$user = Mage::getSingleton("admin/session")->getUser();
		if (!$user) {
			return false;
		}

		$vId = $user->getUdropshipVendor();
		if ($vId) {
			$vendor = Mage::getModel("udropship/vendor")->load($vId);
			if ($vendor->getId()) {
				return $vendor;
			}
		}

		return false;
	}
}
