<?php

class Unirgy_DropshipMicrosite_Model_Url extends Mage_Core_Model_Url
{
    public function getUrl($routePath = null, $routeParams = null)
    {
	    $forceVendorUrl = null;
		$_vendor = isset($routeParams['_vendor']);

	    if ($_vendor) {
		    if ($routeParams['_vendor']) {
			    $forceVendorUrl = true;
		    } else {
			    $forceVendorUrl = $routeParams['_vendor'] === null ? null : false;
		    }
		    unset($routeParams['_vendor']);
	    } else {
		    $forceVendorUrl = !empty($routeParams['_current']);
	    }

	    if ($_vendor) {
		    Mage::app()->getStore()->useVendorUrl($forceVendorUrl);
        } elseif($forceVendorUrl) {
		    Mage::app()->getStore()->useVendorUrl(true);
	    }

        $url = parent::getUrl($routePath, $routeParams);

	    if($_vendor || $forceVendorUrl) {
			Mage::app()->getStore()->resetUseVendorUrl();
		}
	    return $url;
	}
}