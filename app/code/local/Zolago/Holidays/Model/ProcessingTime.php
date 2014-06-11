<?php
class Zolago_Holidays_Model_ProcessingTime extends Mage_Core_Model_Abstract{
	
	protected function _construct() {   
        $this->_init('zolagoholidays/processingtime');
    }
	
	public function updateModelData($data){
		
		try{
			
			if(!empty($data)){
				
				$this->setDays($data['days']);
				$this->setHour($data['hour']);
				$this->setType($data['type']);
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
