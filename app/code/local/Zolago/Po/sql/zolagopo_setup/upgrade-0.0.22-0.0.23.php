<?php
//$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */
//$installer->startSetup();


/**
 * Set default value for configs
 */

Mage::getModel('core/config')->saveConfig("payment/cashondelivery/channel_owner", Zolago_Payment_Model_Source_Channel_Owner::OWNER_VENDOR);
Mage::getModel('core/config')->saveConfig("payment/banktransfer/channel_owner", Zolago_Payment_Model_Source_Channel_Owner::OWNER_MALL);
Mage::getModel('core/config')->saveConfig("payment/dotpay/channel_owner", Zolago_Payment_Model_Source_Channel_Owner::OWNER_MALL);

/**
 * Update info about
 * how money flow (through mall or vendor) for PO
 */

// Get all po collection

/** @var Zolago_Po_Model_Resource_Po_Collection $collection */
$collection = Mage::getResourceModel('zolagopo/po_collection');

foreach($collection as $po) {
    /** @var Zolago_Po_Model_Po $po */

    $owner = 0;
    if ($po->isPaymentCheckOnDelivery()) {
        $owner = Zolago_Payment_Model_Source_Channel_Owner::OWNER_VENDOR;
    } elseif ($po->isPaymentBanktransfer()) {
        $owner = Zolago_Payment_Model_Source_Channel_Owner::OWNER_MALL;
    } elseif ($po->isPaymentDotpay()) {
        $owner = Zolago_Payment_Model_Source_Channel_Owner::OWNER_MALL;
    }
    $po->setData('payment_channel_owner', $owner);
    $po->save();
}