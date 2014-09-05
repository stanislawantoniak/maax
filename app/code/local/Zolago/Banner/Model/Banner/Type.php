<?php

class Zolago_Banner_Model_Banner_Type
{

    const BANNER_TYPES_CONFIG = 'zolagobanner/config/zolagobannertypes';

    const TYPE_SLIDER = 'slider';
    const TYPE_BOX = 'box';
    const TYPE_INSPIRATION = 'inspiration';

    /**
     * @return array
     */
    public function toOptionHash()
    {
        $types = array();

        $typesConfig = $this->getTypesConfig();

        if (!empty($typesConfig)) {
            foreach ($typesConfig as $i => $typesConfigItem) {
                if ((int)$i > 0 && is_object($typesConfigItem)) {
                    $title = $typesConfigItem->title;
                    $code = $this->_prepareBannerTypeCode($title);
                    $types[$code] = $typesConfigItem->title;
                }
            }
        }

        return $types;
    }

    protected function _prepareBannerTypeCode($title)
    {
        $code = str_replace(array('[\', \']'), '', $title);
        $code = preg_replace('/\[.*\]/U', '', $code);
        $code = preg_replace('/&(amp;)?#?[a-z0-9]+;/i', '_', $code);
        $code = htmlentities($code, ENT_COMPAT, 'utf-8');
        $code = preg_replace(
            '/&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);/i', '\\1', $code
        );
        $code = preg_replace(array('/[^a-z0-9]/i', '/[-]+/'), '_', $code);
        return strtolower(trim($code, '_'));
    }


    public function getTypCodeByTitle($title)
    {
        return $this->_prepareBannerTypeCode($title);
    }

    /**
     * @return mixed
     */
    public function getTypesConfig(){
        $configPath = self::BANNER_TYPES_CONFIG;
        $configValue = Mage::getStoreConfig($configPath);
        $typesConfig = Mage::helper('core')->jsonDecode($configValue);
        return $typesConfig;
    }
}