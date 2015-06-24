<?php
/**
 * Update info about
 * how money flow (through mall or vendor) for PO
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

// Get all po collection
/** @var Zolago_Po_Model_Resource_Po_Collection $collection */
$collection = Mage::getResourceModel('zolagopo/po_collection');

foreach($collection as $po) {
    /** @var Zolago_Po_Model_Po $po */
    $owner = 0;
    if ($po->isPaymentCheckOnDelivery()) {
        $owner = Zolago_Payment_Model_Source_Channel_Owner::OWNER_VENDOR;// default settings
    } elseif ($po->isPaymentBanktransfer()) {
        $owner = Zolago_Payment_Model_Source_Channel_Owner::OWNER_MALL;// default settings
    } elseif ($po->isPaymentDotpay()) {
        $owner = Zolago_Payment_Model_Source_Channel_Owner::OWNER_MALL;// default settings
    }
    $po->setData('payment_channel_owner', $owner);
    $po->save();
}

$installer->endSetup();