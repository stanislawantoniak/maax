<?php

/**
 * Class GH_Regulation_Model_Resource_Regulation_Document
 */
class GH_Regulation_Model_Resource_Regulation_Document extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('ghregulation/regulation_document', 'id');
    }

    /**
     * Return documents which not jet active vendor need to accept
     *
     * @param $vendor
     * @param bool $asCollection
     * @return array
     */
    public function getDocumentsToAccept($vendor, $asCollection = false)
    {
        $vendor = $this->getVendor($vendor);
        /** @var GH_Regulation_Helper_Data $helper */
        $helper = Mage::helper("ghregulation");
        $data = $helper->getVendorDocuments($vendor);

        $ids = array();
        $result = array();
        foreach ($data as $kindName => $docs) {
            foreach ($docs as $doc) {
                $ids[] = $doc['id'];
                $result[] = array_merge(array("name" => $kindName), $doc);
                break;
            }
        }
        if ($asCollection) {
            /** @var GH_Regulation_Model_Resource_Regulation_Document_Collection $coll */
            $coll = Mage::getResourceModel("ghregulation/regulation_document_collection");
            $coll->addFieldToFilter('id' ,array('in' => array_unique($ids)));
            return $coll;
        }
        return $result;
    }

    /**
     * @param Zolago_Dropship_Model_Vendor|int $vendor
     * @return Zolago_Dropship_Model_Vendor
     */
    public function getVendor($vendor) {
        if (!($vendor instanceof Zolago_Dropship_Model_Vendor)) {
            $vendor = Mage::getModel('udropship/vendor')->load($vendor);
        }
        return $vendor;
    }
}