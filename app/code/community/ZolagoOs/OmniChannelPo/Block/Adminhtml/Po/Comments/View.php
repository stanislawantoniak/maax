<?php
/**
  
 */
 
class ZolagoOs_OmniChannelPo_Block_Adminhtml_Po_Comments_View extends Mage_Adminhtml_Block_Sales_Order_Comments_View
{
    public function getStatuses()
    {
        $_statuses = Mage::getSingleton('udpo/source')->setPath('po_statuses')->toOptionHash();
        if (!Mage::getStoreConfigFlag('zolagoos/vendor/allow_forced_po_status_change')) {
            $_allowedPoStatuses = Mage::helper('udpo')->getAllowedPoStatuses($this->getEntity(), false);
            $__statuses = array();
            foreach ($_statuses as $_status => $_statusLbl) {
                if (in_array($_status, $_allowedPoStatuses)) {
                    $__statuses[$_status] = $_statusLbl;
                }
            }
        } else {
            $__statuses = $_statuses;
        }
        return $__statuses;
    }
    public function canSendCommentEmail()
    {
        return false;
    }
}