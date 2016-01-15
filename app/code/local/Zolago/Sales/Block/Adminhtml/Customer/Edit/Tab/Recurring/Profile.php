<?php
/**
 * hide tab
 */
class Zolago_Sales_Block_Adminhtml_Customer_Edit_Tab_Recurring_Profile 
    extends Mage_Sales_Block_Adminhtml_Customer_Edit_Tab_Recurring_Profile {
     
    public function isHidden() {
        return true;
    }   
}