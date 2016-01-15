<?php
/**
 * hide tab
 */
class Zolago_Sales_Block_Adminhtml_Customer_Edit_Tab_Agreement 
    extends Mage_Sales_Block_Adminhtml_Customer_Edit_Tab_Agreement {
        
    public function isHidden() {
        return true;
    }
}