<?php
/**
 * helper made for creating shipment in po
 */
class Zolago_Po_Helper_Shipment extends Mage_Core_Helper_Abstract {

    protected $_udpo;
    protected $_carrierData = array ('title' => null,'name'=>null);
    protected $_vendor;
    protected $_track;
    protected $_shipment;
    protected $_number;
    protected $_poStatus;


    /**
     * Set tracking number
     * @param string $number
     * @return void
     */
     public function setNumber($number) {
         $this->_number = $number;
     }

    /**
     * Get tracking number if set
     * @return string
     * @throws Mage_Core_Exception
     */
     public function getNumber() {
         if (empty($this->_number)) {
             Mage::throwException('Tracking number is not set');
         }
         return $this->_number;
     }

    /**
     * Set new PO status
     * @param string $poStatus
     * @return void
     */
     public function setPoStatus($poStatus) {
         $this->_poStatus = empty($poStatus)? '':$poStatus;
     }
     
    /**
     * get new PO status if set
     * @return string
     */
     public function getPoStatus() {
         return $this->_poStatus;
      }

    /**
     * Set PO
     * @param Unirgy_DropshipPo_Model_Po
     * @return void
     */
    public function setUdpo($udpo) {
        $this->_udpo = $udpo;
    }
    
    /**
     * Get PO if set
     * @return Unirgy_DropshipPo_Model_Po
     */
    public function getUdpo() {
        if (empty($this->_udpo)) {
            Mage::throwException('Po not set');
        }
        return $this->_udpo;
    }
    
    /**
     * Set carrier params
     * @param string $carrier
     * @param string $title
     * @return void
     */
    public function setCarrierData($carrier,$title) {
        $this->_carrierData['title'] = $title;
        $this->_carrierData['name'] = $carrier;
    }
    
    /**
     * get carrier name
     * @return string
     */
    public function getCarrierName() {
        $data = $this->_getCarrierData();
        return $data['name'];
    }
    
    /**
     * get waybill title
     * @return string
     */
    public function getCarrierTitle() {
        $data = $this->_getCarrierData();
        return $data['title'];
    }
    
    /**
     * preparing carrier data
     * @return array
     */
    protected function _getCarrierData() {
        $carrier = $this->_carrierData['name'];
        $carrierTitle = $this->_carrierData['title'];
        if (empty($carrier) || empty($carrierTitle)) {
            $shipment = $this->getShipment();
            $store = $this->getUdpo()->getOrder()->getStore();
            $method = explode('_', $shipment->getUdropshipMethod(), 2);
            $this->_carrierData['name'] = $method[0];
            $this->_carrierData['title'] = Mage::getStoreConfig('carriers/'.$method[0].'/title', $store);
        }
        return $this->_carrierData;

    }

    /**
     * @return Mage_Sales_Model_Order_Shipment
     * @throws Mage_Core_Exception
     */
    public function getShipment() {
        if (empty($this->_shipment)) {
            $udpo = $this->getUdpo();
            if(!$udpo->getStatusModel()->isShippingAvailable($udpo)) {
                throw new Mage_Core_Exception(
                    Mage::helper("zolagopo")->__("Shipment cannot be created with this status.")
                );
            }
            $shipment = Mage::helper('udpo')->createShipmentFromPo($udpo, array(), true, true, true);
            if ($shipment) {
                $shipment->setNewShipmentFlag(true);
                $shipment->setDeleteOnFailedLabelRequestFlag(true);
                $shipment->setCreatedByVendorFlag(true);
            } else {
                Mage::throwException("Cannot create shipment");
            }
            $this->_shipment = $shipment;
        }
        return $this->_shipment;
    }

    /**
     * Gets track
     * @return Mage_Sales_Model_Order_Shipment_Track|false
     */
    public function getTrack($requestData=null) {
        if (empty($this->_track)) {
            $number = $this->getNumber();
            $carrier = $this->getCarrierName();
            Mage::log($requestData, null, "12.log");
            $title = $this->getCarrierTitle();

	        /** @var Mage_Sales_Model_Order_Shipment_Track $track */
	        $track = Mage::getModel('sales/order_shipment_track');
            $track
	            ->setData('qty',1)
	            ->setNumber($number)
	            ->setCarrierCode($carrier)
	            ->setTitle($title);
	            
	        if(!is_null($requestData)) {
		        switch($title) {
			        case 'DHL':
						/** @var Orba_Shipping_Helper_Carrier_Dhl $_dhlHlp */
						$_dhlHlp = Mage::helper('orbashipping/carrier_dhl');
                        Mage::log($requestData['shipping_source_account'], null, "12.log");

                        if(isset($requestData['shipping_source_account'])){
                            $shipping_source_account = $requestData['shipping_source_account'];
                            Mage::log($shipping_source_account, null, "12.log");
                            $track->setData("shipping_source_account",$shipping_source_account);
                        }
				        $weight = $_dhlHlp->getDhlParcelWeightByKey($requestData['specify_orbadhl_rate_type']);
						$track->setWeight($weight);
						if(isset($requestData['specify_orbadhl_size'])) {
							$dimensions = $_dhlHlp->getDhlParcelDimensionsByKey($requestData['specify_orbadhl_size']);
							$track
								->setWidth($dimensions[0])
								->setHeight($dimensions[1])
								->setLength($dimensions[2]);
						} else {
							$track
								->setWidth(0)
								->setHeight(0)
								->setLength(0);
						}
                        if(isset($requestData['gallery_shipping_source']) && $requestData['gallery_shipping_source'] == 1) {
                            $track->setGalleryShippingSource(1);
                        }

				        break;
			        default:
				        break;
		        }
	        }

            $this->_track = $track;
        }
        return $this->_track;
    }
    
    public function prepareUdpoStatuses() {
        $udpoStatuses = false;
        if (Mage::getStoreConfig('udropship/vendor/is_restrict_udpo_status')) {
            $udpoStatuses = Mage::getStoreConfig('udropship/vendor/restrict_udpo_status');
            if (!is_array($udpoStatuses)) {
                $udpoStatuses = explode(',', $udpoStatuses);
            }
        }
        return $udpoStatuses;
    }

    /**
     * Connecting track to shipment
     * @return void
     */
     public function processSaveTracking($requestData=null) {

         $track = $this->getTrack($requestData);
         Mage::log($track->getData());
         $shipment = $this->getShipment();
         $carrier = $this->getCarrierName();
         $vendor = $this->getVendor();
         $udpo = $shipment->getUdpo();
         $codValue = $udpo->getGrandTotalInclTax() - $udpo->getPaymentAmount();
         $totalValue = $udpo->getGrandTotalInclTax();
         $manager = Mage::helper('orbashipping')->getShippingManager($carrier);
         $type = empty($requestData['specify_orbadhl_rate_type'])? 0:$requestData['specify_orbadhl_rate_type'];
         $manager->calculateCharge($track,$type,$vendor,$totalValue,$codValue);
         $shipment->addTrack($track,$requestData);
         $number = $this->getNumber();
         $isShipped = $this->getShippedFlag();
          Mage::helper('udropship')->processTrackStatus($track, true, $isShipped);
          Mage::helper('udropship')->addShipmentComment(
             $shipment,
             $this->__('%s added tracking ID %s', $vendor->getVendorName(), $number)
         );         
         $shipment->save();
             // Carrier saved
         $udpo = $this->getUdpo();
         $udpo->setCurrentCarrier($carrier);
         $udpo->getResource()->saveAttribute($udpo, "current_carrier");            
     }

    /**
     * @return bool
     */
     public function getShippedFlag() {
        $udpo = $this->getUdpo();
        $store = $udpo->getOrder()->getStore();
        $autoComplete = Mage::getStoreConfig('udropship/vendor/auto_shipment_complete', $store);

        $poStatusShipped = Unirgy_DropshipPo_Model_Source::UDPO_STATUS_SHIPPED;
        $poStatusDelivered = Unirgy_DropshipPo_Model_Source::UDPO_STATUS_DELIVERED;
        
        $poStatus = $this->getPoStatus();        

        $isShipped = $poStatus == $poStatusShipped || $poStatus==$poStatusDelivered || $autoComplete && ($poStatus==='' || is_null($poStatus));
        return $isShipped;

     }
     
    /**
     * @param Unirgy_Dropship_Model_Vendor
     * @return void
     */
     public function setVendor($vendor) {
         $this->_vendor = $vendor;
     }

    /**
     * @return Unirgy_Dropship_Model_Vendor
     */
     public function getVendor() {
         if (empty($this->_vendor)) {
             $udpo = $this->getUdpo();
             $this->_vendor = Mage::helper('udropship')->getVendor($udpo->getUdropshipVendor());
         }
         return $this->_vendor;
     }
     
    /**
     * 
     * @return bool
     */
     public function checkChangeStatus() {         
        $udpoStatuses = $this->prepareUdpoStatuses();
        $udpo = $this->getUdpo();        
        $poStatus = $this->getPoStatus();

        if (
            !is_null($poStatus) && 
            $poStatus!=='' && 
            $poStatus!=$udpo->getUdropshipStatus()&& 
            (
                !$udpoStatuses || 
                (
                    in_array($udpo->getUdropshipStatus(), $udpoStatuses) && 
                    in_array($poStatus, $udpoStatuses)
                )
            )
        ) {
            return true;
        } else {
            return false;
        }
        
     }
    /**    
     * Save new status after save tracking
     * @return void
     */
    public function processSetStatus() {

        $poStatusShipped   = Unirgy_DropshipPo_Model_Source::UDPO_STATUS_SHIPPED;
        $poStatusDelivered = Unirgy_DropshipPo_Model_Source::UDPO_STATUS_DELIVERED;
        $poStatusCanceled  = Unirgy_DropshipPo_Model_Source::UDPO_STATUS_CANCELED;

        $udpo = $this->getUdpo();
        
        $oldStatus = $udpo->getUdropshipStatus();

        if ($oldStatus==$poStatusCanceled && !$udpo->getForceStatusChangeFlag()) {
             Mage::throwException(Mage::helper('udpo')->__('Canceled purchase order cannot be reverted'));
        }

        $hlp 	  = Mage::helper('udropship');
        $udpoHlp  = Mage::helper('udpo');
        $poStatus = $this->getPoStatus();
        $vendor   = $this->getVendor();
        
        switch ($poStatus) {
            case $poStatusCanceled:
                $udpoHlp->cancelPo($udpo, true, $vendor);
                $poStatusChanged = $udpoHlp->processPoStatusSave($udpo, $poStatus, true, $vendor);
                break;
            case $poStatusShipped:
            case $poStatusDelivered:                
                foreach ($udpo->getShipmentsCollection() as $_s) {
                    $hlp->completeShipment($_s, true, $poStatus==$poStatusDelivered);
                }
                if (isset($_s)) {
                    $hlp->completeOrderIfShipped($_s, true);
                }
            default:
                $poStatusChanged = $udpoHlp->processPoStatusSave($udpo, $poStatus, true, $vendor);            
        }
       $udpo->getCommentsCollection()->save();
       return $poStatusChanged;
   }                

    /**
     * Finishing save shipment
     * @throws Mage_Core_Exception
     */
   public function invoiceShipment() {
       $shipment = $this->getShipment();
       if ($shipment->getNewShipmentFlag() && !$shipment->isDeleted()) {
            $shipment->setNoInvoiceFlag(false);
            Mage::helper('udpo')->invoiceShipment($shipment);
        }
    }

}