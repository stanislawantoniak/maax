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

                $this->setReturnReasonId($data['return_reason_id']);
                $this->setVendorId($data['vendor_id']);
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
}