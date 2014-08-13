<?php

class Zolago_Banner_Model_Banner_Type
{

    const BANNER_TYPES_CONFIG = 'zolagobanner/config/zolagobannertypes';

    /**
     * @return array
     */
    public function toOptionHash()
    {
        $types = array();
        $configPath = self::BANNER_TYPES_CONFIG;
        $configValue = Mage::getStoreConfig($configPath);
        $typesConfig = json_decode($configValue);

        if (!empty($typesConfig)) {
            foreach ($typesConfig as $typesConfigItem) {
                $title = $typesConfigItem->title;
                $code = $this->_prepareBannerTypeCode($title);
                $types[$code] = $typesConfigItem->title;
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
}