<?php
class Zolago_Pos_Helper_Data extends Mage_Core_Helper_Abstract{
    
	protected $posCache = array();
	
	public function isValidForVendor($pos, $vendor) {
		if ($pos instanceof Zolago_Pos_Model_Pos) {
			$pos = $pos->getId();
		}
		if ($vendor instanceof Zolago_Dropship_Model_Vendor) {
			$vendor = $vendor->getId();
		}

		if (empty($this->posCache) && !isset($this->posCache[$vendor])) {
			/** @var Zolago_Pos_Model_Resource_Pos_Collection $coll */
			$coll = Mage::getResourceModel('zolagopos/pos_collection');
			$coll->addVendorFilter($vendor);
			
			$data = $coll->getData();
			
			foreach ($data as $_pos) {
				if (!empty($_pos['external_id'])) {
					$this->posCache[$vendor][$_pos['external_id']] = true;
				}
			}
		}
		
		return isset($this->posCache[$vendor]) && isset($this->posCache[$vendor][$pos]);
	}
} 