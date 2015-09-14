<?php
class Zolago_Campaign_Model_Campaign_Urltype {

    const TYPE_MANUAL_LINK = 0;
    const TYPE_LANDING_PAGE = 1;

    /**
     * @return array
     */
    public function toOptionHash()
    {
        return array(
            self::TYPE_MANUAL_LINK => Mage::helper("zolagocampaign")->__("Manual link"),
            self::TYPE_LANDING_PAGE => Mage::helper("zolagocampaign")->__("Landing page")
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