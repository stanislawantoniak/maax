<?php

class Zolago_Campaign_Block_Vendor_Campaign_Placement_Category extends Mage_Core_Block_Template
{

    protected function _beforeToHtml()
    {
        return parent::_beforeToHtml();
    }

    /**
     * @return Unirgy_Dropship_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('udropship/session');
    }

    public function getCampaigns(){
        $vendor = Mage::getSingleton('udropship/session')->getVendor();
        $vendor = $vendor->getId();
        /* @var $vendor Unirgy_Dropship_Model_Vendor */
        $campaign = Mage::getResourceModel("zolagocampaign/campaign");
        $campaignBank = $campaign->getCampaigns();

        $campaigns = array();
        //prepare campaigns group by type
        foreach($campaignBank as $campaign){
            $campaigns[$campaign["banner_type"]][] = array(
                'campaign_id' => $campaign['campaign_id'],
                'name' => $campaign['name'],
                'date_from' => !empty($campaign['date_from']) ? date("d.m.Y H:i:s",$campaign['date_from']) : '',
                'date_to' => !empty($campaign['date_to']) ? date("d.m.Y H:i:s",$campaign['date_to']) : ''
            );
        }
        return $campaigns;
    }

    public function getCategoryId()
    {
        $category = $this->getRequest()->getParam('category', null);
        return $category;
    }
}