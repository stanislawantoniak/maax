<?php
class Zolago_Holidays_Model_Holiday extends Mage_Core_Model_Abstract{
	
	protected function _construct() {   
        $this->_init('zolagoholidays/holiday');
    }
	
	public function updateModelData($data){
		
		try{
			
			if(!empty($data)){
				
				//is fixed
				if($data['type'] == '1'){
					$date_array = explode("/", $data['date']);
					$date_array = array_splice($date_array, 0, 2);
					$data['date'] = implode("/", $date_array);
				}
				
				$this->setCountryId($data['country_id']);
				$this->setName($data['name']);
				$this->setType($data['type']);
				$this->setDate($data['date']);
				$this->setExcludeFromDelivery(isset($data['exclude_from_delivery']) ? 1 : 0);
				$this->setExcludeFromPickup(isset($data['exclude_from_pickup']) ? 1 : 0);
				$this->setCreatedAt(time());
				$this->setUpdatedAt(time());
			}else{
				throw new Exception("Error Processing Request: Insuficient Data Provided.");		
			}
		} catch (Exception $e){
			Mage::logException($e);
		}
		
		return $this;
	}
}
