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

        /* @var $vendor Unirgy_Dropship_Model_Vendor */
        $campaign = Mage::getResourceModel("zolagocampaign/campaign");
        $campaignBank = $campaign->getCampaigns();

        $campaigns = array();
        //prepare campaigns group by type
        foreach($campaignBank as $campaign){
            $campaigns[$campaign["banner_type"]][] = array(
                'campaign_id' => $campaign['campaign_id'],
                'name' => $campaign['name'],
                'date_from' => !empty($campaign['date_from']) ? date("d.m.Y H:i:s",strtotime($campaign['date_from'])) : '',
                'date_to' => !empty($campaign['date_to']) ? date("d.m.Y H:i:s",strtotime($campaign['date_to'])) : ''
            );
        }
        return $campaigns;
    }

    /**
     * @return array
     */
    public function getCategoryPlacements()
    {
        $vendor = Mage::getSingleton('udropship/session')->getVendor();
        $vendorId = $vendor->getId();

        $categoryId = $this->getCategoryId();
        $campaign = Mage::getResourceModel("zolagocampaign/campaign");
        $placements = $campaign->getCategoryPlacements($categoryId, $vendorId);

        $placementsByType = array();
        if (!empty($placements)) {
            foreach ($placements as $placement) {
                $placement['campaign_date_from'] = !empty($placement['campaign_date_from']) ? date("d.m.Y H:i:s", strtotime($placement['campaign_date_from'])) : '';
                $placement['campaign_date_to'] = !empty($placement['campaign_date_to']) ? date("d.m.Y H:i:s", strtotime($placement['campaign_date_to'])) : '';
                $placementsByType[$placement['type']][] = $placement;
            }
        }

        return $placementsByType;
    }

    public function getCategoryId()
    {
        $category = $this->getRequest()->getParam('category', null);
        return $category;
    }

    public function getCategoryName()
    {
        $categoryName = '';
        $category = $this->getRequest()->getParam('category', null);
        if (!empty($category)) {
            $categoryModel = Mage::getModel('catalog/category');
            $categoryObj = $categoryModel->load($category);
            $categoryName = $categoryObj->getName();
        }
        return $categoryName;
    }
}