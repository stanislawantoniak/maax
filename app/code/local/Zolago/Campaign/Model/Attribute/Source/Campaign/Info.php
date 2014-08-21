<?php

class Zolago_Campaign_Model_Attribute_Source_Campaign_Info extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getAllOptions()
    {
        $campaignModel = Mage::getModel("zolagocampaign/campaign");
        $campaigns = $campaignModel->getProductCampaignInfo();

        $options = $this->_prepareCampaignOptions($campaigns);
        if (is_null($this->_options)) {
            $this->_options = $options;
        }

        return $this->_options;
    }

    public function toOptionArray()
    {
        return $this->getAllOptions();
    }

    private function _prepareCampaignOptions($campaigns)
    {
        $options = array();
        if (!empty($campaigns)) {
            foreach ($campaigns as $campaign) {
                $options[] = array(
                    'label' => $campaign['campaign_name'],
                    'value' => $campaign['campaign_id']
                );
            }
        }
        return $options;
    }
}