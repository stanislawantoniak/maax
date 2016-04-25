<?php

class ZolagoOs_SimpleLicense_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function checkUpdates()
    {

    }
    public static function validateLicense() {
            $module = "Unirgy_DropshipMicrosite";
			$key = "VN29643YBOFNSD86R2VOEWYEIF" . microtime(true);
			ZolagoOs_SimpleLicense_Helper_Protected::obfuscate($key);
			$hash = ZolagoOs_SimpleLicense_Helper_Protected::validateModuleLicense($module);
			var_Dump($hash); // for log
			return true;
    }
}