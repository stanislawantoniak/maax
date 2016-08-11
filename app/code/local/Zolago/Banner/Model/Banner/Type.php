<?php

class Zolago_Banner_Model_Banner_Type
{

    const BANNER_TYPES_CONFIG = 'zolagobanner/config/zolagobannertypes';

    const TYPE_SLIDER = 'slider';
    const TYPE_BOX = 'box';
    const TYPE_INSPIRATION = 'inspiration';

    const TYPE_LANDING_PAGE_CREATIVE = 'landing_page_creative';

    /**
     * @return array
     */
    public function toOptionHash()
    {
        $types = array();

        $typesConfig = $this->getTypesConfig();

        /* @var $_zolagoDropshipHelper Zolago_Dropship_Helper_Data */
        $_zolagoDropshipHelper = Mage::helper("zolagodropship");

        if (!empty($typesConfig)) {
            foreach ($typesConfig as $i => $typesConfigItem) {
                if ((int)$i > 0 && is_array($typesConfigItem)) {
                    $title = $typesConfigItem['title'];
                    $code = $this->_prepareBannerTypeCode($title);

                    $onlyForLocalVendor = isset($typesConfigItem["only_for_local_vendor"]) ? 1 : 0;
                    if(!$onlyForLocalVendor){
                        $types[$code] = $typesConfigItem['title'];
                    } else {
                        if ($_zolagoDropshipHelper->isLocalVendor()) {
                            $types[$code] = $typesConfigItem['title'];
                        }
                    }

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

    /**
     * Define if Banner type editable for not LOCAL VENDOR
     * @param $type
     * @return bool
     */
    public function isTypeDefinitionAvailableVorLocalVendor($type)
    {

        $availableForAll = true;
        $typesConfig = $this->getTypesConfig();
        foreach ($typesConfig as $key => $typesConfigItem) {
            if (is_int($key)) {
                if ($this->getTypCodeByTitle($typesConfigItem["title"]) == $type
                    && isset($typesConfigItem["only_for_local_vendor"])
                ) {
                    $availableForAll = false;
                }
            }

        }
        return $availableForAll;
    }
}