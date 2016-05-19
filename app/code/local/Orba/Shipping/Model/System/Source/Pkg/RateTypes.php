<?php
class Orba_Shipping_Model_System_Source_Pkg_RateTypes {
	/* VENDOR'S DHL RATES */
	/* those are keys from /app/code/local/Zolago/Dropship/etc/config.xml around line 320 */
	const DHL_RATES_ENVELOPE = 'dhl_rates_envelope';
	const DHL_RATES_PARCEL_0_5 = 'dhl_rates_parcel_0_5';
	const DHL_RATES_PARCEL_5_10 = 'dhl_rates_parcel_5_10';
	const DHL_RATES_PARCEL_10_20 = 'dhl_rates_parcel_10_20';
	const DHL_RATES_PARCEL_20_31_5 = 'dhl_rates_parcel_20_31_5';

	protected $_hashes = array(
		self::DHL_RATES_ENVELOPE => "Envelope",
		self::DHL_RATES_PARCEL_0_5 => "Parcel 0-5kg",
		self::DHL_RATES_PARCEL_5_10 => "Parcel 5-10kg",
		self::DHL_RATES_PARCEL_10_20 => "Parcel 10-20kg",
		self::DHL_RATES_PARCEL_20_31_5 => "Parcel 20-31.5kg"
	);

	public function toOptionHash() {
		$out = array();
		foreach($this->_hashes as $value=>$label){
			$out[$value] = Mage::helper('orbashipping')->__($label);
		}
		return $out;
	}


}