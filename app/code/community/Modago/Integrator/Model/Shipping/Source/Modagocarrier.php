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
			array('value' => 'ups', 'label' => 'UPS')
			// TODO add others
		);
		if(!$isMultiselect){
			array_unshift($data, array('value'=>'', 'label'=>''));
		}
		return $data;
	}
}
