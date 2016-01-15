<?php

/**
 * Source for all carriers from Modago
 *
 * Class Modago_Integrator_Model_Payment_Source_Carrier
 */
class Modago_Integrator_Model_Shipping_Source_Modagocarrier {

	public function toOptionArray($isMultiselect = false) {
		$data = array(
			array('value' => 'dhl', 'label' => 'DHL'),
			array('value' => 'ups', 'label' => 'UPS'),
			array('value' => 'dpd', 'label' => 'DPD'),
			array('value' => 'siodemka', 'label' => 'Siódemka'),
			array('value' => 'gls', 'label' => 'GLS'),
			array('value' => 'fedex', 'label' => 'Fedex'),
			array('value' => 'poczta_polska', 'label' => 'Poczta Polska'),
			array('value' => 'inpost', 'label' => 'Inpost'),
		);

		if(!$isMultiselect){
			array_unshift($data, array('value'=>'', 'label'=>''));
		}
		return $data;
	}
}
