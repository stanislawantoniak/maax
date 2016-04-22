<?php
/**
  
 */

abstract class ZolagoOs_OmniChannel_Model_Label_Abstract_Type extends Varien_Object
{
    /**
    * Send batch file PDF download
    *
    */
    public function printBatch($batch=null)
    {
        $data = $this->renderBatchContent($batch);
        Mage::helper('udropship')->sendDownload($data['filename'], $data['content'], $data['type']);
    }

    /**
    * Send PDF download only for 1 track
    *
    * @param Mage_Sales_Model_Order_Shipment_Track $track
    */
    public function printTrack($track=null)
    {
        $data = $this->renderTrackContent($track);
        Mage::helper('udropship')->sendDownload($data['filename'], $data['content'], $data['type']);
    }

    public function getBatchPathName($batch)
    {
        return Mage::getConfig()->getVarDir('batch').DS.$this->getBatchFileName($batch);
    }

    protected function _getTrackVendorId($track)
    {
        $vId = null;
        if ($track instanceof ZolagoOs_Rma_Model_Rma_Track) {
            $vId = $track->getRma()->getUdropshipVendor();
        } elseif ($track instanceof Mage_Sales_Model_Order_Shipment_Track) {
            $vId = $track->getShipment()->getUdropshipVendor();
        }
        return $vId;
    }

    protected function _getTrackVendor($track)
    {
        return Mage::helper('udropship')->getVendor($this->_getTrackVendorId($track));
    }
}