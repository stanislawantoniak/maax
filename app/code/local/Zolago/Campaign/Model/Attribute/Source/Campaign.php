<?php

class Zolago_Campaign_Model_Attribute_Source_Campaign extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    protected $_useCustomOptions = false;

    public function getAllOptions($withEmpty = true, $defaultValues = false)
    {
        if (!$this->_options || $this->_useCustomOptions) {
            /* @var $campaignModel Zolago_Campaign_Model_Campaign */
            $campaignModel = Mage::getModel("zolagocampaign/campaign");
            $campaigns = $campaignModel->getProductCampaign();
            $this->_options = $this->_prepareCampaignOptions($campaigns);
        }

        return $this->_options;
    }

    public function toOptionArray()
    {
        return $this->getAllOptions();
    }

    /**
     * format options
     * @param $campaigns
     * @return array
     */
    private function _prepareCampaignOptions($campaigns)
    {
        $options = array( 0 => array(
            'label' => '',
            'value' => ''
        ));
        if (!empty($campaigns)) {
            foreach ($campaigns as $campaign) {
                $options[] = array(
                    'label' => $this->_useCustomOptions ? $campaign['campaign_id'] : $campaign['campaign_name'],
                    'value' => $campaign['campaign_id']
                );
            }
        }
        return $options;
    }

    /**
     * Force getAllOptions to serve custom variables for solr/faces
     * When indexing/updating process
     * @param $value
     */
    public function setUseCustomOptions($value) {
        $this->_useCustomOptions = $value;
    }
}