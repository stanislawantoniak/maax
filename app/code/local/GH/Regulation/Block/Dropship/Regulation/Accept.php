<?php

/**
 * Class GH_Regulation_Block_Dropship_Regulation_Accept
 */
class GH_Regulation_Block_Dropship_Regulation_Accept extends Mage_Core_Block_Template
{

    /**
     * @return string
     * @throws Exception
     */
    public function getAcceptBlock()
    {
        $vendor = $this->getVendor();
        // loading the static block
        $block = Mage::getModel('cms/block')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load('vendor_regulations_accept');
        /* @var $block Mage_Cms_Model_Block */

        // setting the assoc. array we send to the filter.
        $array = array();

        $array['vendor'] = $vendor;

        // loading the filter which will get the array we created and parse the block content
        $filter = Mage::getModel('cms/template_filter');
        /* @var $filter Mage_Cms_Model_Template_Filter */
        $filter->setVariables($array);

        // return the filtered block content.
        return $filter->filter($block->getContent());

    }

    /**
     * @return array
     */
    public function getDocumentsToAccept()
    {
        return Mage::helper("ghregulation")->getDocumentsToAccept($this->getVendor());
    }

    /**
     * @return string
     */
    public function getSaveVendorDocumentUrl() {
        return $this->getUrl("udropship/vendor/saveVendorDocumentPost", array("_secure" => true));
    }

    /**
     * @return ZolagoOs_OmniChannel_Model_Vendor
     * @throws Exception
     */
    public function getVendor()
    {
        $id = $this->getRequest()->getParam('id', false);
        /* @var $vendor ZolagoOs_OmniChannel_Model_Vendor */
        $vendor = Mage::getModel('udropship/vendor')->load($id);

        return $vendor;
    }

    public function maxUploadInMB() {
        /* @var $ghCommonHelper GH_Common_Helper_Data */
        $ghCommonHelper = Mage::helper('ghcommon');
        $minByte = $ghCommonHelper->getMaxUploadFileSize();
        return round($minByte / (1024*1024), 1, PHP_ROUND_HALF_DOWN); // to MB
    }
}