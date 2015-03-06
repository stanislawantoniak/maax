<?php
/**
 * rma types
 */
class Zolago_Rma_Model_System_Source_Type {
    protected $hashes = array (
        Zolago_Rma_Model_Rma::RMA_TYPE_STANDARD => 'Standard',
        Zolago_Rma_Model_Rma::RMA_TYPE_RETURN   => 'Returned by courier',
    );
    
    
    /**
     * list of types for html options
     * @return array
     */

    public function toOptionHash() {
        $out = array();
        foreach ($this->hashes as $key=>$val) {
            $out[$key] = Mage::helper('zolagorma')->__($val);
        }
        return $out;
    }
    
    /**
     * returns type name by id 
     *
     * @return string
     */

    public function getTypeById($id) {
        return !isset($this->hashes[$id])? '':$this->hashes[$id];
    }
}