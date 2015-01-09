<?php
class Zolago_Campaign_Model_Campaign_Strikeout {

    const STRIKEOUT_TYPE_PREVIOUS_PRICE = 1;
    const STRIKEOUT_TYPE_MSRP_PRICE = 2;

	/**
	 * @return array
	 */
	public function toOptionArray() {
        $helper = Mage::helper('zolagocampaign');
		$out = array();
        $out[] = array('value' => self::STRIKEOUT_TYPE_PREVIOUS_PRICE, 'label' => $helper->__('Previous price'));
        $out[] = array('value' => self::STRIKEOUT_TYPE_MSRP_PRICE, 'label' => $helper->__('MSRP price'));
		return $out;
	}

    /**
     * @return array
     */
    public function toOptionHash() {
        $helper = Mage::helper('zolagocampaign');
        return array(
            self::STRIKEOUT_TYPE_PREVIOUS_PRICE => $helper->__('Previous price'),
            self::STRIKEOUT_TYPE_MSRP_PRICE => $helper->__('MSRP price')
        );
    }
}