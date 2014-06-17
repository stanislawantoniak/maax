<?php
class Zolago_Rma_Model_VendorReturnReason extends Mage_Core_Model_Abstract{

    protected function _construct() {
        $this->_init('zolagorma/vendorreturnreason');
    }

    /**
     * @param mixed[] @data
     *
     * @throws Exception When array is empty and when cannot set values
     *
     * @return Zolago_Rma_Model_VendorReturnReason
     */
    public function updateModelData($data){

        try{

            if(!empty($data)){

                if(key_exists('return_reason_id', $data)) $this->setReturnReasonId($data['return_reason_id']);
                if(key_exists('vendor_id', $data)) $this->setVendorId($data['vendor_id']);
                $this->setAutoDays($data['auto_days']);
                $this->setAllowedDays($data['allowed_days']);
                $this->setMessage($data['message']);
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
	
	/**
     * 
     * @return Zolago_Rma_Model_ReturnReason
     */
	public function getReturnReason(){
		
		return Mage::getModel('zolagorma/returnreason')->load($this->getReturnReasonId());
		
	}
}