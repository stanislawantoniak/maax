<?php

class Zolago_Campaign_Model_Campaign extends Mage_Core_Model_Abstract
{
    const ZOLAGO_CAMPAIGN_ID_CODE = "campaign_regular_id";
    const ZOLAGO_CAMPAIGN_INFO_CODE = "campaign_info_id";

    protected function _construct()
    {
        $this->_init("zolagocampaign/campaign");
    }

    /**
     * @param array $data
     * @return boolean|array
     */
    public function validate($data = null)
    {
        if ($data === null) {
            $data = $this->getData();
        } elseif ($data instanceof Varien_Object) {
            $data = $data->getData();
        }

        if (!is_array($data)) {
            return false;
        }

        $errors = Mage::getSingleton("zolagocampaign/campaign_validator")->validate($data);

        if (empty($errors)) {
            return true;
        }
        return $errors;
    }
    /**
     * @return array
     */
    public function getAllowedWebsites() {
        if(!$this->hasData("website_ids")){
            $allowedWebsites = array();
            if($this->getId()){
                $allowedWebsites = $this->getResource()->getAllowedWebsites($this);
            }
            $this->setData("website_ids", $allowedWebsites);
        }
        return $this->getData("website_ids");
    }

    /**
     * @return array
     */
    public function getCampaignProducts() {
        if(!$this->hasData("campaign_products")){
            $campaignProducts = array();
            if($this->getId()){
                $campaignProducts = $this->getResource()->getCampaignProducts($this);
            }
            $this->setData("campaign_products", implode("," , $campaignProducts));
        }
        return $this->getData("campaign_products");
    }

    /*
     * @return array
     */
    public function getProductCampaign($productId) {
        if (empty($productId)) {
            return array();
        }
        return $this->getResource()->getProductCampaign($productId);
    }

    /*
 * @return array
 */
    public function getCampaigns() {
        return $this->getResource()->getCampaigns();
    }
}