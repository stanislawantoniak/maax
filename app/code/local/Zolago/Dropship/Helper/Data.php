<?php
class Zolago_Dropship_Helper_Data extends Unirgy_Dropship_Helper_Data {
	const TRACK_SINGLE			= 1;
	protected $trackingHelperPath = 'orbashipping/carrier_tracking';
	
	
	/**
	 * @return array
	 */
	public function getAllowedStores($vendor) {
		$allowed = array();
		$limitedWebsites = $vendor->getLimitWebsites();
		
		if(!is_array($limitedWebsites)){
			$realWebsites = array();
		}elseif(!count($limitedWebsites)){
			$realWebsites = array();
		}elseif(count($limitedWebsites)==1 && $limitedWebsites[0]==""){
			$realWebsites = array();
		}else{
			foreach($limitedWebsites as $websiteId){
				if($websiteId){
					$realWebsites[] = $websiteId;
				}
			}
		}
		foreach(Mage::app()->getStores() as $store){
			if($realWebsites && in_array($store->getWebsiteId(), $realWebsites)){
				$allowed[] = $store;
			}elseif(!$realWebsites){
				$allowed[] = $store;
			}
		}
		return $allowed;
	}
	
	
    /**
     * @param string
     */
	public function setTrackingHelperPath($path) {
		$this->trackingHelperPath  = $path;
		
	}

	/**
	 * @param Unirgy_Rma_Model_Rma_Track | string $tracking 
	 */
	public function getTrackingStatusName($tracking) {
		if($tracking instanceof Unirgy_Rma_Model_Rma_Track){
			$tracking = $tracking->getUdropshipStatus();
		}
		switch ($tracking) {
			case Unirgy_Dropship_Model_Source::TRACK_STATUS_CANCELED:
				return $this->__("Canceled");
			break;
			case Unirgy_Dropship_Model_Source::TRACK_STATUS_DELIVERED:
				return $this->__("Delivered");
			break;
			case Unirgy_Dropship_Model_Source::TRACK_STATUS_PENDING:
				return $this->__("Pending");
			break;
			case Unirgy_Dropship_Model_Source::TRACK_STATUS_READY:
				return $this->__("Ready");
			break;
			case Unirgy_Dropship_Model_Source::TRACK_STATUS_SHIPPED:
				return $this->__("Shipped");
			break;
		}
		return $tracking;
	}
	
	/**
	 * @param Mage_Core_Model_Store|int|null $store
	 * @return Mage_Catalog_Model_Entity_Attribute
	 */
	public function getVendorSkuAttribute($store=null) {
		if($store instanceof Mage_Core_Model_Store){
			$store=$store->getId();
		}
		$attrCode = Mage::getStoreConfig('udropship/vendor/vendor_sku_attribute', $store);
		if(!empty($attrCode)){
			$attr = Mage::getSingleton('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attrCode);
			if($attr->getId()){
				return $attr;
			}
		}
		return Mage::getSingleton('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, "sku");
	}
	
	public function getAllowedCarriers() {
        $allowCarriers = Mage::helper('orbashipping/carrier_tracking')->getTrackingCarriersList();
        return $allowCarriers;	
	}
	
    //{{{ 
    /**
     * 
     * @param Zolago_Pos_Model_Pos $pos
     * @return 
     */
    public function getAllowedCarriersForPos($pos) {
        $out = array();
        if ($pos->getUseDhl()) {
            $out[Orba_Shipping_Model_Carrier_Dhl::CODE] = Orba_Shipping_Model_Carrier_Dhl::CODE;
        }
        if ($pos->getUseOrbaups()) {
            $out[Orba_Shipping_Model_Carrier_Ups::CODE] = Orba_Shipping_Model_Carrier_Ups::CODE;
        }
        return $out;
    }
    //}}}
    //{{{ 
    /**
     * 
     * @param Unirgy_Dropship_Model_Vendor $vendor
     * @param bool $rmaMode 
     * @return array
     */
     public function getAllowedCarriersForVendor($vendor,$rmaMode = false) {
         $out = array();
         if ($vendor->getUseDhl()) {
            $out[Orba_Shipping_Model_Carrier_Dhl::CODE] = Orba_Shipping_Model_Carrier_Dhl::CODE;
         }
         if ($vendor->getDhlRma() && $rmaMode) {
            $out[Orba_Shipping_Model_Carrier_Dhl::CODE] = Orba_Shipping_Model_Carrier_Dhl::CODE;
         }
         if ($vendor->getUseOrbaups()) {
            $out[Orba_Shipping_Model_Carrier_Ups::CODE] = Orba_Shipping_Model_Carrier_Ups::CODE;
         }
         if ($vendor->getOrbaupsRma() && $rmaMode) {
            $out[Orba_Shipping_Model_Carrier_Ups::CODE] = Orba_Shipping_Model_Carrier_Ups::CODE;
         }
         return array_unique($out);
     }

    //}}}
	public function isUdpoMpsAvailable($carrierCode, $vendor = null) {
        $allowCarriers = Mage::helper('orbashipping/carrier_tracking')->getTrackingCarriersList();
		if(in_array($carrierCode, $allowCarriers)){
			return true;
		}
		return parent::isUdpoMpsAvailable($carrierCode, $vendor);
	}
	
    /**
     * Poll carriers tracking API
     *
     * @param mixed $tracks
     */
    public function collectTracking($tracks)
    {
        $requests = array();
        foreach ($tracks as $track) {
            $cCode = $track->getCarrierCode();
            if (!$cCode) {
                continue;
            }
            $vId = $track->getShipment()->getUdropshipVendor();
            $v = Mage::helper('udropship')->getVendor($vId);
            $allowCarriers = Mage::helper('orbashipping/carrier_tracking')->getTrackingCarriersList();
			if (!in_array($cCode,$allowCarriers)) {
				if (!$v->getTrackApi($cCode) || !$v->getId()) {
					continue;
				}
			}
			
			if (!$vId) {
				continue;
			}
			
			$requests[$cCode][$vId][$track->getNumber()][] = $track;
        }
        foreach ($requests as $cCode => $vendors) {        	
            foreach ($vendors as $vId => $trackIds) {
                $_track = null;
                foreach ($trackIds as $_trackId=>$_tracks) {
                    foreach ($_tracks as $_track) break 2;
                }
                try {
                    if ($_track) Mage::helper('udropship/label')->beforeShipmentLabel($v, $_track);
                    $helper = Mage::helper($this->trackingHelperPath);
                    switch ($cCode) {
                        case Orba_Shipping_Model_Carrier_Dhl::CODE:
                            $helper->setHelper(Mage::helper('orbashipping/carrier_dhl'));
    						$result = $helper->collectTracking($trackIds);
                            break;
                        case Orba_Shipping_Model_Carrier_Ups::CODE:
                            $helper->setHelper(Mage::helper('orbashipping/carrier_ups'));
    						$result = $helper->collectTracking($trackIds);
                            break;
                        default:
    						$result = $v->getTrackApi($cCode)->collectTracking($v, array_keys($trackIds));
                    }
                    if ($_track) Mage::helper('udropship/label')->afterShipmentLabel($v, $_track);
                } catch (Exception $e) {
                    if ($_track) Mage::helper('udropship/label')->afterShipmentLabel($v, $_track);
                    $this->_processPollTrackingFailed($trackIds, $e);
                    continue;
                }
				
				if(!is_array($result)) continue;
				
                $processTracks = array();
                foreach ($result as $trackId=>$status) {
                    foreach ($trackIds[$trackId] as $track) {

                        if (in_array($status, array(Unirgy_Dropship_Model_Source::TRACK_STATUS_PENDING,Unirgy_Dropship_Model_Source::TRACK_STATUS_READY,Unirgy_Dropship_Model_Source::TRACK_STATUS_SHIPPED))) {
                            $repeatIn = Mage::getStoreConfig('udropship/customer/repeat_poll_tracking', $track->getShipment()->getOrder()->getStoreId());
                            if ($repeatIn<=0) {
                                $repeatIn = 12;
                            }
                            $repeatIn = $repeatIn*60*60;
                            $track->setNextCheck(date('Y-m-d H:i:s', time()+$repeatIn))->save();
                            if ($status==Unirgy_Dropship_Model_Source::TRACK_STATUS_PENDING) continue;
                        }
                        $track->setUdropshipStatus($status);
						
                        if ($track->dataHasChangedFor('udropship_status')) {
                            switch ($status) {
                            case Unirgy_Dropship_Model_Source::TRACK_STATUS_READY:
                                Mage::helper('udropship')->addShipmentComment(
                                    $track->getShipment(),
                                    $this->__('Tracking ID %s was picked up from %s', $trackId, $v->getVendorName())
                                );
                                $track->getShipment()->save();
                                break;

                            case Unirgy_Dropship_Model_Source::TRACK_STATUS_DELIVERED:
                                Mage::helper('udropship')->addShipmentComment(
                                    $track->getShipment(),
                                    $this->__('Tracking ID %s was delivered to customer', $trackId)
                                );
                                $track->setShippedDate();
                                $track->save();
                                $track->getShipment()->save();
                                break;
                            }
                            if (empty($processTracks[$track->getParentId()])) {
                                $processTracks[$track->getParentId()] = array();
                            }
                            $processTracks[$track->getParentId()][] = $track;
                        }
                    }
                }
                
                foreach ($processTracks as $_pTracks) {
                    try {
                        $this->processTrackStatus($_pTracks, true); } catch
                    (Exception $e) {
                        $this->_processPollTrackingFailed($_pTracks, $e);
                        continue;
                    }
                }
            }
        }
    }
	
	
	
	public function getProductStatusForVendor(Unirgy_Dropship_Model_Vendor $vendor) {
		$status = Mage_Catalog_Model_Product_Status::STATUS_DISABLED;

		if ($vendor->getReviewStatus()) {
			$status = (int)$vendor->getReviewStatus();
		}
		
		return $status;
	}

	/**
	 * @param Zolago_Dropship_Model_Vendor $vendor
	 * @param int $width
	 * @param int $height
	 * @return null|string
	 */
    public function getVendorLogoResizedUrl($vendor, $width, $height)
    {
		if(!$vendor->getLogo()){
			return null;
		}
		return Mage::helper('udropship')->getResizedVendorLogoUrl($vendor, $width, $height);
    }
}