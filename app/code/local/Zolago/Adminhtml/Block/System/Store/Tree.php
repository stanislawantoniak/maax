<?php

/**
 * Class Zolago_Adminhtml_Block_System_Store_Tree
 */
class Zolago_Adminhtml_Block_System_Store_Tree extends Mage_Adminhtml_Block_System_Store_Tree
{

    /**
     * Render website
     *
     * @param Mage_Core_Model_Website $website
     * @return string
     */
    public function renderWebsite(Mage_Core_Model_Website $website)
    {
        $vendorOwner = "";
        if ($website->getVendorId()) {
            $vendor = Mage::getModel("udropship/vendor")->load($website->getVendorId());
            $vendorName = $this->escapeHtml($vendor->getVendorName());

            $label = $this->__('Vendor Owner');
            $vendorOwner = "<br>{$label}: <span style='background-color:#ff0000;color:#ffffff;font-weight: bold;'>{$vendorName}</span>";
        }

        return $this->_createCellTemplate()
            ->setObject($website)
            ->setLinkUrl($this->getUrl('*/*/editWebsite', array('website_id' => $website->getWebsiteId())))
            ->setInfo($this->__('Code') . ': ' . $this->escapeHtml($website->getCode()) . $vendorOwner)
            ->toHtml();
    }

}