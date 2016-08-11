<?php
/**
 * block with regulations history
 */
class GH_Regulation_Block_Dropship_Rules
    extends Mage_Core_Block_Template {

    
    /**
     * returns list of vendor documents
     *
     * @return array
     */
     public function getDocumentList() {
        $vendor = $this->getVendor();
        $documents =  Mage::helper('ghregulation')->getVendorDocuments($vendor);
        ksort($documents);
        return $documents;
     }

	public function getVendor() {
		return Mage::getSingleton('udropship/session')->getVendor();
	}
    
}