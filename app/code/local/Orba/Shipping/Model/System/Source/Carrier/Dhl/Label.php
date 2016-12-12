<?php
class Orba_Shipping_Model_System_Source_Carrier_Dhl_Label {

	const LP = 'LP';
        const BLP = 'BLP';
	const ZBLP = 'ZBLP';

	public function toOptionHash() {
		$out = array(
		    self::LP => self::LP,
		    self::BLP => self::BLP,
		    self::ZBLP => self::ZBLP,
		);
		return $out;
	}
	    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => self::LP, 'label'=>self::LP),
            array('value' => self::BLP, 'label'=>self::BLP),
            array('value' => self::ZBLP, 'label'=>self::ZBLP),
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
    	return $this->toOptionHash();
    }



}