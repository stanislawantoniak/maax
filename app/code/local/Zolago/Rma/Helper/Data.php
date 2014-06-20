<?php
class Zolago_Rma_Helper_Data extends Unirgy_Rma_Helper_Data {

    /**
     * @param Zolago_Rma_Model_Rma $rma
     * @param string $status
     * @return Zolago_Rma_Model_Rma
     */
    public function processSaveStatus(Zolago_Rma_Model_Rma $rma, $status) {
        $oldStatus = $rma->getRmaStatus();
        if($status!=$oldStatus) {
            $rma->setRmaStatus($status);
            $rma->getResource()->saveAttribute($rma, 'rma_status');
            // Trigger event
            Mage::dispatchEvent("zolagorma_rma_status_changed", array(
                                    "rma"			=> $rma,
                                    "new_status"	=> $status,
                                    "old_status"	=> $oldStatus
                                ));
        }
        return $rma;
    }

    /**
     *
     * @param type $items
     * @return type
     */
    public function getItemList($items) {
        $out = array();
        $child = array();
        foreach ($items as $item) {
            if ($parentId = $item->getParentItemId()) {
                $child[$parentId][]  = $item;
            }
        }
        foreach ($items as $item) {
            $max = intval($item->getQty());
            if (!$item->getParentItemId()) {
                for ($a = 0; $a<$max; $a++) {
                    $entity_id = $item->getEntityId();
                    if (!empty($child[$item->getOrderItemId()])) {
                        $name = '';
                        foreach ($child[$item->getOrderItemId()] as $ch) {
                            $name .= $ch->getName();
                        }
                    } else {
                        $name = $item->getName();
                    }
                    $out[$entity_id][$a] = array (
                                               'entityId' => $entity_id,
                                               'name' => $name,
                                           );
                }
            }
        }
        return $out;

    }

    /**
     * tracking rma
     */
    public function rmaTracking() {
        $statusFilter = array(Unirgy_Dropship_Model_Source::TRACK_STATUS_PENDING,Unirgy_Dropship_Model_Source::TRACK_STATUS_READY,Unirgy_Dropship_Model_Source::TRACK_STATUS_SHIPPED);
        $res  = Mage::getSingleton('core/resource');
        $conn = $res->getConnection('sales_read');
        $sIdsSel = $conn->select()->distinct()
                   ->from($res->getTableName('urma/rma_track'), array('parent_id'))
                   ->where('udropship_status in (?)', $statusFilter)
                   ->where('next_check<=?', now())
                   ->limit(50);
        echo $sIdsSel;
        $sIds = $conn->fetchCol($sIdsSel);
        print_R($sIds);
        die();

    }
	
	/**
	 * @return array
	 */
	public function getItemConditionTitles(){
		$collection = Mage::getModel('zolagorma/rma_reason')->getCollection();
		return $collection->toOptionHash();
	} 
	
	public function getReturnReasons($po, $to_json){
		
		$reasons_array = array();
		
		$vendor = $po->getVendor();
		
		$vendor_reasons = Mage::getModel('zolagorma/rma_reason_vendor')->getCollection()
															->addFieldToFilter('vendor_id', $vendor->getId());
		
		if($vendor_reasons->count() > 0){
			
			foreach($vendor_reasons as $vendor_reason){
				
				$return_reason_id = $vendor_reason->getReturnReasonId();
				
				$reasons_array[$return_reason_id] = array(
					'isAvailable' => $this->isReasonAvailable($return_reason_id, $po),
					'message' => $vendor_reason->getMessage()
				);
				
			}
		}				
		
		return ($to_json) ? json_encode($reasons_array) : $reasons_array;	
	}
	
	public function isReasonAvailable($return_reason_id, $po){
		
		$vendor = $po->getVendor();
		
		$reason_vendor = Mage::getModel('zolagorma/rma_reason_vendor')->getCollection()
															          ->addFieldToFilter('return_reason_id', $return_reason_id)
															          ->addFieldToFilter('vendor_id', $vendor->getId())
															          ->getFirstItem();
																  
		if($reason_vendor){
			
			//now
 			$time_now = new Zend_Date();
			
			$shipped_date = Mage::getModel('sales/order_shipment_track')->getShippedDate();
			
		    $time_then = new Zend_Date("2014-05-21T10:30:00");
		    $difference = $time_now->sub($time_then);
		
		    $measure = new Zend_Measure_Time($difference->toValue(), Zend_Measure_Time::SECOND);
		    $measure->convertTo(Zend_Measure_Time::DAY);
		
		    $days_elapsed = $measure->getValue();
			
			//Acknowledged return days #
			$acknowledged_return_days = $reason_vendor->getAllowedDays();
			
			return ($days_elapsed > $acknowledged_return_days) ? false : true;
		}
		
		return false;
	}
} 
