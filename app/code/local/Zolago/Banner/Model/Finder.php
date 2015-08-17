<?php

class Zolago_Banner_Model_Finder extends Varien_Object
{
	
	public function __construct() {
		$inItems = func_get_arg(0);
		
		if(!is_array($inItems)){
			Mage::throwException("No banner items");
		}
		
		parent::__construct(array(
			"items" => $inItems
		));
	}
	
	/**
	 * @param Varien_Object $request - 
	 *      banner_show - string (required)
	 *		type - string (required)
	 *		slots - int (required)
	 * @return array
	 */
	public function request($request) {
		
		$items = $this->getItems();
		
		$out = array();
		$status = $request->getStatus();
		$date = $request->getDate();
		$type = $request->getType();
		$bannerShow = $request->getBannerShow();
		foreach ($items as $item) {
		    if ($status !== null) {
		        if ($item['campaign_status'] != $status) {
		            continue;
		        }
		    }
		    if ($date !== null) {
		        if ($item['campaign_date_from']) {
		            if ($date<$item['campaign_date_from']) {
		                continue;
		            }
		        }
		        if ($item['campaign_date_to']) {
		            if ($date>$item['campaign_date_to']) {
		                continue;
		            }
		        }
		        if ($type !== null) {
		            if ($item['type'] != $type) {
		                continue;
		            }
		        }
		        if ($bannerShow !== null) {
		            if ($item['banner_show'] != $bannerShow) {
		                continue;
		            }
		        }
		    }

            /** @var Zolago_Dropship_Model_Vendor $vendor */
            $vendor = $item["vendor"];
            if ($vendor) {
                $localVendorId = Mage::helper('udropship')->getLocalVendorId();
                $url = $vendor->getUrlKey() . "/" . $item["campaign_url"];
                $item["campaign_url"] = $item["campaign_vendor"] == $localVendorId ? $item["campaign_url"] : $url;
            }

		    if (empty($out[$item['position']]) || ($out[$item['position']]['priority']>$item['priority'])) {
    		    $out[$item['position']] = $item;		    
		    }
		}
		if ($out) {	
		    ksort($out);
		}
        return array_values($out);
	}
	

}