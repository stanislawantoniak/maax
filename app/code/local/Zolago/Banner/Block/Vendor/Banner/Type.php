<?php

class Zolago_Banner_Block_Vendor_Banner_Type extends Mage_Core_Block_Template
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return array
     */
    public function getTypes()
    {
        $types = array_merge(
            array(0 => Mage::helper("zolagobanner")->__("--- Select ---")),
            Mage::getSingleton('zolagobanner/banner_type')->toOptionHash()
        );
        return $types;
    }

    public function getCampaignId()
    {
        return $this->getRequest()->getParam("campaign_id", 0);
    }

    public function getCampaignName()
    {
        $campaignId = $this->getCampaignId();
        $model = Mage::getModel("zolagocampaign/campaign")->load($campaignId);
        return $model->getName();
    }
}