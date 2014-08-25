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
        $collection = Mage::getResourceModel("zolagocampaign/campaign_collection");
        $collection->getSelect()
            ->join( array(
                'banner' => 'zolago_banner'),
                'banner.campaign_id=main_table.campaign_id',
                array('banner_type' => "banner.type")
            );
        $collection->addFieldToFilter('main_table.vendor_id',(int)$vendor);
        $collection->setOrder('main_table.date_from','DESC');
        //$collection->printLogQuery(true);

        $campaigns = array();
        //prepare campaigns group by type
        foreach($collection as $campaign){
            $campaigns[$campaign->getData("banner_type")][] = $campaign;
        }
        return $campaigns;
    }
}