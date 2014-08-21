<?php

class Zolago_Banner_Helper_Data extends Mage_Core_Helper_Abstract {

    public function setBannerTypeUrl(){

        return Mage::getUrl('zolagobanner/vendor/setType');
    }

    public function bannerTypeUrl($campaignId)
    {
        return Mage::getUrl('zolagobanner/vendor/type', array('campaign_id' => $campaignId));
    }

    public function bannerEditUrl($campaignId, $type)
    {
        return Mage::getUrl('zolagobanner/vendor/edit', array('type' => $type, 'campaign_id' => $campaignId));
    }

}