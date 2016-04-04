<?php

/* @var $this Mage_Core_Model_Resource_Setup */
$this->startSetup();

$errorMsg = "Something went wrong during GH_Inpost upgrade-0.0.1-0.0.2.php";

//create delivery type
/** @var Unirgy_DropshipTierShipping_Model_DeliveryType $dropshipDeliveryType */
$dropshipDeliveryType = Mage::getModel('udtiership/deliveryType');
$dropshipDeliveryType->setData(array(
	'delivery_code' => 'ghinpost',
	'delivery_title' => 'Paczkomaty inPost',
	'sort_order' => 20
));
// $dropshipDeliveryType->save();

if(!$dropshipDeliveryType->getId()) {
	Mage::throwException($errorMsg);
}

//create shipping method
/** @var Unirgy_DropshipSh $dropshipShipping */
$dropshipShipping = Mage::getModel("udropship/shipping");
$dropshipShipping->setData(array(
	'shipping_code' => 'ghinpost',
	'shipping_title' => 'Paczkomaty inPost',
	'days_in_transit' => 1,
	'vendor_ship_class' => 1,
	'customer_ship_class' => 1
));
// $dropshipShipping->save();

if(!$dropshipShipping->getId()) {
	Mage::throwException($errorMsg);
}

$this->endSetup();