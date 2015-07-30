<?php
class Zolago_Campaign_Model_Attribute_Source_Campaign_LandingPageContext {

    const LANDING_PAGE_CONTEXT_VENDOR = 'vendor';
    const LANDING_PAGE_CONTEXT_GALLERY = "gallery";

    /**
     * @return array
     */
    public function toOptionHash()
    {
        return array(
            self::LANDING_PAGE_CONTEXT_VENDOR => Mage::helper("zolagocampaign")->__("VENDOR "),
            self::LANDING_PAGE_CONTEXT_GALLERY => Mage::helper("zolagocampaign")->__("GALLERY")
        );
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