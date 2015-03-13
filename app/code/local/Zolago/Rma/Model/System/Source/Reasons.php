<?php
/**
 * select for config returned reasons
 */
class Zolago_Rma_Model_System_Source_Reasons extends Varien_Object {
    
    /**
     * option list
     * @return array
     */
     public function toOptionArray() {
         $collection = Mage::getResourceModel('zolagorma/rma_reason_collection')->load();
         return $collection->toOptionHash();
     }

}