<?php
class Zolago_Campaign_Model_Attribute_Source_Campaign_LandingPageContext {

    const LANDING_PAGE_CONTEXT_VENDOR = 'vendor';
    const LANDING_PAGE_CONTEXT_GALLERY = "gallery";

    /**
     * @return array
     */
    public function toOptionHash()
    {
        $optionHashArray = array();
        /* @var $_zolagoCommonHelper Zolago_Common_Helper_Data */
        $_zolagoCommonHelper = Mage::helper("zolagocommon");
        $useGalleryConfig = $_zolagoCommonHelper->useGalleryConfiguration();
        if($useGalleryConfig){
            $optionHashArray = array(
                self::LANDING_PAGE_CONTEXT_VENDOR => Mage::helper("zolagocampaign")->__("VENDOR"),
                self::LANDING_PAGE_CONTEXT_GALLERY => Mage::helper("zolagocampaign")->__("GALLERY")
            );
        }else{
            $optionHashArray = array(
                self::LANDING_PAGE_CONTEXT_GALLERY => Mage::helper("zolagocampaign")->__("GALLERY")
            );
    }
        return $optionHashArray;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = array();
        $optionsHash = $this->toOptionHash();
        if (!empty($optionsHash)) {
            foreach ($optionsHash as $value => $label) {
                $options[] = array('value' => $value, 'label' => $label);
            }
        }
        return $options;
    }
    
}