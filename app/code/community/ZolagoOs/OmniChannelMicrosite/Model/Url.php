<?php

class ZolagoOs_OmniChannelMicrosite_Model_Url extends Mage_Core_Model_Url
{
    public function getUrl($routePath = null, $routeParams = null)
    {
		// Forcing no vendor
		$noVendor = false;
		if(isset($routeParams['_no_vendor'])){
			unset($routeParams['_no_vendor']);
			$noVendor = true;
		}
		
		$forceVendorUrl = !empty($routeParams['_current']);
        if ($forceVendorUrl) {
            Mage::app()->getStore()->useVendorUrl(true);
        }
        $url = parent::getUrl($routePath, $routeParams);
        if ($forceVendorUrl) {
            Mage::app()->getStore()->resetUseVendorUrl();
        }
		
		
		// Recaculate url if need to remove vendor
		if($noVendor){
			$url = Mage::helper("zolagodropshipmicrosite")->convertToNonVendorContext($url);
		}
		
        return $url;
	}
}