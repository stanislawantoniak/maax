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
    public function getMaxFileSize(){
        $uploadMaxFileSize= ini_get('upload_max_filesize');
        return $this->returnKBytes($uploadMaxFileSize)-1;
    }

    /**
     * @param $val
     *
     * @return int|string
     */
    function returnKBytes($val)
    {
        $val = trim($val);
        $last = strtolower($val[strlen($val) - 1]);
        switch ($last) {
            case "g":
                $val *= 1024 *1024;
                break;
            case "m":
                $val *= 1024;
                break;
            case "k":
                $val *= 1;
                break;
        }
        return $val;
    }
}