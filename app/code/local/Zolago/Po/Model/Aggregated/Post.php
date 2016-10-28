<?php
/**
 * pdf for aggregated documents for poczta polska
 */
class Zolago_Po_Model_Aggregated_Post 
    extends Orba_Common_Model_Pdf {

    const PO_AGGREGATED_PATH = 'shipping';
    const PO_AGGREGATED_PREFIX = 'aggregate_';


    public function _getFilePrefix() {
        return self::PO_AGGREGATED_PREFIX;
    }
    public function _getFilePath() {
        return self::PO_AGGREGATED_PATH;
    }
    
    protected function _preparePages($id) {
        Mage::throwException(Mage::helper('orbashipping')->__('Document not generated yet. You should send post packet'));
    }
    public function saveFile($file,$id) {
        $filename = $this->_getFileName($id);
        file_put_contents($filename,$file);
    }
    

}