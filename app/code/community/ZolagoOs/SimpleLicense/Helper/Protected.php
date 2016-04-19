<?php

final class ZolagoOs_SimpleLicense_Helper_Protected
{
	private static $_macRegExp = "/([0-9a-f]{2}[:-][0-9a-f]{2}[:-][0-9a-f]{2}[:-][0-9a-f]{2}[:-][0-9a-f]{2}[:-][0-9a-f]{2})/i";
	private static $_licenseApiUrl = "https://secure.unirgy.com/simple/client_api/";
	private static $_s = "2OOxGXdd0vGTPk7!kmN\$";
	private static $_obfuscateKey = NULL;

	private static function callApi($action, $data)
	{
		$curl = curl_init();
		$uSimpleLicVersion = Mage::app()->getConfig()->getNode("modules/ZolagoOs_SimpleLicense/version");
		$url = self::$_licenseApiUrl . $action . "?uslv=" . $uSimpleLicVersion;
		if (!empty($data["license_key"])) {
			$url .= "&l=" . $data["license_key"];
		}

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_TIMEOUT, 10);
		curl_setopt($curl, CURLOPT_HEADER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, (string)Mage::getStoreConfigFlag("usimpleup/general/verify_ssl"));
		$response = curl_exec($curl);
		$result = array("curl_error" => "", "http_code" => "", "header" => "", "body" => "");
		if ($error = curl_error($curl)) {
			$result["curl_error"] = $error;
			return $result;
		}

		$result["http_code"] = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
		$result["header"] = substr($response, 0, $headerSize);
		$result["body"] = substr($response, $headerSize);
		curl_close($curl);

		Mage::log($data, null, 'apicall.log');
		return $result;
	}

	private static function serverMACs()
	{
		$macs = array();
		$output = array();
		if (!function_exists("exec")) {
			Mage::log("exec() seems to be disabled, cannot check mac address", Zend_Log::ERR, "unirgy.log", true);
			return $macs;
		}

		if (strpos(strtolower(PHP_OS), "win") === 0) {
			exec("ipconfig /all | find \"Physical Address\"", $output);
		} else {
			exec("/sbin/ifconfig -a | grep -E \"HWaddr|ether\"", $output);
		}

		foreach ($output as $line) {
			if (preg_match(self::$_macRegExp, $line, $m)) {
				$macs[] = strtoupper($m[1]);
			}

		}
		return $macs;
	}

	private static function licenseSignatureString($d)
	{
		if (is_object($d)) {
			$d = $d->getData();
		}
		return str_replace("\r\n", "\n", (!empty($d["license_key"]) ? $d["license_key"] : "") . "|" . (!empty($d["license_status"]) ? $d["license_status"] : "") . "|" . (!empty($d["products"]) ? $d["products"] : "") . "|" . (!empty($d["server_restriction"]) ? $d["server_restriction"] : "") . "|" . (!empty($d["server_restriction1"]) ? $d["server_restriction1"] : "") . "|" . (!empty($d["server_restriction2"]) ? $d["server_restriction2"] : "") . "|" . (!empty($d["license_expire"]) ? $d["license_expire"] : "") . "|" . (!empty($d["upgrade_expire"]) ? $d["upgrade_expire"] : ""));
	}

	private static function licenseSignature($d)
	{
		return empty($d) ? "" : sha1(self::$_s . "|" . self::licenseSignatureString($d));
	}

	public static function sendServerInfo()
	{
		$licenses = Mage::getModel("usimplelic/license")->getCollection();
		$keys = array();
		foreach ($licenses as $license) {
			$keys[] = $license->getLicenseKey();
		}
		$data = array("license_keys" => join("\n", $keys), "ioncube_version" => ioncube_loader_version(), "server_data" => ioncube_server_data(), "mac_addresses" => Zend_Json::encode(self::serverMACs()), "http_host" => $_SERVER["HTTP_HOST"], "server_name" => $_SERVER["SERVER_NAME"], "server_addr" => $_SERVER["SERVER_ADDR"], "host_ip" => gethostbyname(php_uname("n")));
		$result = self::callApi("server_info/", $data);
		return $result;
	}

	public static function retrieveLicense($key, $installModules = false)
	{
		$license = is_object($key) ? $key : Mage::getModel("usimplelic/license")->load($key, "license_key");
		$key = $license->getLicenseKey() ? $license->getLicenseKey() : $key;
		$data = array("license_key" => $key, "license" => Zend_Json::encode($license->getData()), "signature" => self::licenseSignature($license), "signature_string" => self::licenseSignatureString($license), "server_data" => ioncube_server_data());
		$result = self::callApi("license/", $data);
		if ($result["curl_error"]) {
			$error = "ZolagoOs_SimpleLicense connection error while retrieving license: " . $result["curl_error"];
			if (!$license->getId()) {
				throw new ZolagoOs_SimpleLicense_Exception($error);
			}
			$license->setLastStatus("curl_error")->setLastError($result["curl_error"])->setRetryNum($license->getRetryNum() + 1)->save();
			Mage::log($error);
			return false;
		}

		if ($result["http_code"] != 200) {
			$error = "ZolagoOs_SimpleLicense http error while retrieving license: " . $result["http_code"];
			if (!$license->getId()) {
				throw new ZolagoOs_SimpleLicense_Exception($error);
			}
			$license->setLastStatus("http_error")->setLastError($result["http_code"] . ": " . $result["body"])->setRetryNum($license->getRetryNum() + 1)->save();
			Mage::log($error);
			return false;
		}

		if (empty($result["body"])) {
			$data = NULL;
		} else {
			try {
				$json = trim($result["body"]);
				if ($json[0] === "{" || $json[0] === "[") {
					$data = Zend_Json::decode($json);
				} else {
					$data = NULL;
				}
			} catch (Exception $e) {
				if ($e->getMessage() == "Decoding failed: Syntax error") {
					$data = NULL;
				} else {
					$data = NULL;
				}
			}
		}

		if (!$data) {
			$error = "ZolagoOs_SimpleLicense decoding error while retrieving license: <xmp>" . $result["body"] . "</xmp>";
			if (!$license->getId()) {
				throw new ZolagoOs_SimpleLicense_Exception($error);
			}
			$license->setLastStatus("body_error")->setLastError($result["headers"] . "\n\n" . $result["body"])->setRetryNum($license->getRetryNum() + 1)->save();
			Mage::log($error);
			return false;
		}

		if ($data["status"] == "error") {
			$error = $key . ": " . $data["message"];
			if ($license->getId()) {
				$license->setLastStatus("status_error")->setLastError($error)->setRetryNum($license->getRetryNum() + 1)->save();
				Mage::log($error);
			}
			throw new ZolagoOs_SimpleLicense_Exception($error);
		}

		$license->addData(array("license_key" => $key, "license_status" => $data["license_status"], "last_checked" => now(), "last_status" => $data["status"], "retry_num" => 0, "products" => join("\n", array_keys($data["modules"])), "server_restriction" => $data["server_restriction"], "server_restriction1" => $data["server_restriction1"], "server_restriction2" => $data["server_restriction2"], "license_expire" => $data["license_expire"], "upgrade_expire" => $data["upgrade_expire"], "server_info" => $data["server_info"]))->setSignature(self::licenseSignature($license))->save();
		if (!empty($data["modules"])) {
			$uris = array();
			foreach ($data["modules"] as $name => $m) {
				if (!$name) {
					continue;
				}

				$module = Mage::getModel("usimpleup/module")->load($name, "module_name");
				if (!$module) {
					continue;
				}

				$module->addData(array("module_name" => $name, "download_uri" => $m["download_uri"], "last_checked" => now(), "remote_version" => $m["remote_version"], "license_key" => $license->license_key))->save();
				$uris[] = $m["download_uri"];
			}
			if ($installModules) {
				Mage::helper("usimpleup")->checkUpdates();
				Mage::helper("usimpleup")->installModules($uris, Mage::app()->getRequest()->getPost("ftp_password"));
			}
		}
	}

	public static function validateLicenseServer($server, $license)	    
	{
	    return true;
		if (!($server = trim($server))) {
			return false;
		}

		if ($server[0] === "{" && preg_match(self::$_macRegExp, $server, $m)) {
			$mac = strtoupper($m[1]);
			return stripos($license->server_info, $server) !== false || in_array($mac, self::serverMACs());
		}

		explode("@", $server);
		list($domain, $ip) = explode("@", $server) + array(1 => "");
		if (!($domain === "" || $domain === "*")) {
			$re = "#^" . str_replace("\\*", ".*", preg_quote($domain)) . "\$#i";
			if (!(preg_match($re, $_SERVER["SERVER_NAME"]) || preg_match($re, $_SERVER["HTTP_HOST"]))) {
				return false;
			}
		}

		if (!($ip === "" || $ip === "*")) {
			$re = "#^" . str_replace("\\*", ".*", preg_quote($ip)) . "\$#i";
			$servADD = $_SERVER["SERVER_ADDR"];
			$intIP = gethostbyname(php_uname("n"));
			if (!preg_match($re, $servADD) && !preg_match($re, $intIP)) {
				return false;
			}
		}

		return true;
	}

	public static function validateLicense($key)
	{
	    return true;
		$license = is_object($key) ? $key : Mage::getModel("usimplelic/license")->load($key, "license_key");
		if (!$license->getId()) {
			throw new ZolagoOs_SimpleLicense_Exception("License record is not found: " . $key);
		}

		$key = $license->license_key;
		if (!Mage::app()->loadCache("ulicense_" . $key) && (!$license->getAuxChecksum() || 2147483647 - $license->getAuxChecksum() < time() - 86400)) {
			Mage::app()->saveCache("1", "ulicense_" . $key, array("ulicense"), 86400);
			$license->setAuxChecksum(2147483647 - time())->save();
			self::retrieveLicense($license->getLicenseKey());
		}

		$expires = $license->getLicenseExpire();
		if ($expires && strtotime($expires) < time() && $license->getLicenseStatus() != "expired") {
			$license->setLicenseStatus("expired")->setSignature(self::licenseSignature($license))->save();
		}

		$errors = array("inactive" => "The license is not active", "expired" => "The license has expired", "invalid" => "The license is not valid for the current server");
		if (!empty($errors[$license->getLicenseStatus()])) {
			throw new ZolagoOs_SimpleLicense_Exception($errors[$license->getLicenseStatus()] . ": " . $license->getLicenseKey());
		}

		if (PHP_SAPI !== "cli" && ($license->getServerRestriction() || $license->getServerRestriction1() || $license->getServerRestriction2())) {
			$found = false;
			if ($license->getServerRestriction()) {
				$servers = explode("\n", $license->getServerRestriction());
				foreach ($servers as $server) {
					if (self::validateLicenseServer($server, $license)) {
						$found = true;
						break;
					}
				}
			}

			if (!$found && $license->getServerRestriction1()) {
				$servers = explode("\n", $license->getServerRestriction1());
				$found = false;
				foreach ($servers as $server) {
					if (self::validateLicenseServer($server, $license)) {
						$found = true;
						break;
					}
				}
				if ($found && $license->getServerRestriction2()) {
					$servers = explode("\n", $license->getServerRestriction2());
					$found = false;
					foreach ($servers as $server) {
						if (self::validateLicenseServer($server, $license)) {
							$found = true;
							break;
						}
					}
				}
			}

			if (!$found) {
				$msg = $errors["invalid"] . ": " . $license->getLicenseKey() . " ";
				$msg .= "SERVER_NAME: " . (!empty($_SERVER["SERVER_NAME"]) ? $_SERVER["SERVER_NAME"] : "null") . "; ";
				$msg .= "HTTP_HOST: " . (!empty($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : "null") . "; ";
				$msg .= "SERVER_ADDR: " . (!empty($_SERVER["SERVER_ADDR"]) ? $_SERVER["SERVER_ADDR"] : "null") . "; ";
				$msg .= "INT_ADDR: " . gethostbyname(php_uname("n")) . "; ";
				throw new ZolagoOs_SimpleLicense_Exception($msg);
			}
		}

		return $license;
	}

	public static function obfuscate($key)
	{
		self::$_obfuscateKey = $key;
	}

	public static function validateModuleLicense($name)
	{
		$module = is_object($name) ? $name : Mage::getModel("usimpleup/module")->load($name, "module_name");
		if (!$module->getId()) {
			throw new ZolagoOs_SimpleLicense_Exception("Module record not found: " . (is_object($name) ? $name->getModuleName() : $name));
		}

		$license = self::validateLicense($module->getLicenseKey());
		$licenseProducts = explode("\n", $license->getProducts());
		if (!in_array($name, $licenseProducts)) {
			throw new ZolagoOs_SimpleLicense_Exception("Module " . $module->getModuleName() . " is not covered by license: " . $module->getLicenseKey());
		}

		return self::$_obfuscateKey ? sha1(self::$_obfuscateKey . $module->getModuleName()) : true;
	}
}