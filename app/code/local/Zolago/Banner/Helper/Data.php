<?php

class Zolago_Banner_Helper_Data extends Mage_Core_Helper_Abstract {

    public function setBannerTypeUrl(){

        return Mage::getUrl('zolagobanner/vendor/setType', array("_secure" => true));
    }

    public function bannerTypeUrl($campaignId)
    {
        return Mage::getUrl('zolagobanner/vendor/type', array('campaign_id' => $campaignId,"_secure" => true));
    }

    public function bannerEditUrl($campaignId, $type)
    {
        return Mage::getUrl('zolagobanner/vendor/edit', array('type' => $type, 'campaign_id' => $campaignId, "_secure" => true));
    }

    public function getBannersConfiguration()
    {
        //fetch config
        $configPath = Zolago_Banner_Model_Banner_Type::BANNER_TYPES_CONFIG;
        $configValue = Mage::getStoreConfig($configPath);
        $typesConfig = json_decode($configValue);

        return $typesConfig;
    }

    /**
     * @return int|string
     */
    public function getMaxFileSize() {
        /* @var $ghCommonHelper GH_Common_Helper_Data */
        $ghCommonHelper = Mage::helper('ghcommon');
        $maxBytes = $ghCommonHelper->getMaxUploadFileSize();
        $maxKBytes = $maxBytes * 1024;
        return $maxKBytes - 1;
    }
}