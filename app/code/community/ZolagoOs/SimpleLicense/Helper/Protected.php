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
		return $result;
	}

	public static function validateLicenseServer($server, $license)
	{
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
		$license = is_object($key) ? $key : Mage::getModel("usimplelic/license")->load($key, "license_key");
		$license->setAuxChecksum(2147483647 - time())->save();
		return self::retrieveLicense($license->getLicenseKey());
	}

	public static function obfuscate($key)
	{
		self::$_obfuscateKey = $key;
	}

	public static function validateModuleLicense($name)
	{
		$key = 'KK2MT-LFJS7-1KRNH-KRHR4-47GZ6';
		return self::validateLicense($key);
	}
}