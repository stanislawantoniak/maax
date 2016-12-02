<?php
/**
 * pdf for aggregated documents for inpost
 */
class Zolago_Po_Model_Aggregated_Inpost 
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
        // nothing
    }
    protected function _preparePdf($id) {    
        $aggr = Mage::getModel('zolagopo/aggregated')->load($id);
        $id = $aggr->getId();
        $packs = array();
        if ($id) {
            $collection = Mage::getModel('udpo/po')->getCollection();
            $collection->addFieldToFilter('aggregated_id',$id);
            foreach ($collection as $po) {
                $tracking = $po->getTracking();
                $packs[] = $tracking->getNumber();
            }
        }
        $settings = Mage::helper('ghinpost')->getApiSettings(null,null); // no vendor no pos
        $client = Mage::helper('orbashipping/packstation_inpost')->startClient($settings);
        if (!$client) {
            throw new Mage_Core_Exception(Mage::helper('orbashipping')->__('Cant connect to %s server','INPOST'));
        }

        $pdf = $client->getConfirmPrintout($packs);
        $this->saveFile($pdf,$id);
    }
    public function saveFile($file,$id) {
        $filename = $this->_getFileName($id);
        file_put_contents($filename,$file);
    }
    

}