<?php

class Zolago_Campaign_Block_Vendor_Campaign_Placement extends Mage_Core_Block_Template
{

    protected function _beforeToHtml()
    {
        return parent::_beforeToHtml();
    }

    /**
     * @return ZolagoOs_OmniChannel_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('udropship/session');
    }

    public function getVendor(){
        return Mage::getSingleton('udropship/session')->getVendor();
    }

    public function getVendorCategoriesList()
    {
        return Mage::helper('zolagocampaign')->getVendorCategoriesList();
    }

    /**
     * @param $catId
     * @return array
     */
    public function getAllChildren($catId)
    {
        return Mage::helper('zolagocampaign')->getAllChildren($catId);
    }


    public function getCategoriesDisplayModePage($cats)
    {
        return Mage::helper('zolagocampaign')->getAllChildren($cats);
    }

}