<?php
/**
 * Update info about
 * how money flow (through mall or vendor) for RMA
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

// Get all rma collection
/* @var $collection Zolago_Rma_Model_Resource_Rma_Collection */
$collection = Mage::getResourceModel('zolagorma/rma_collection');

foreach($collection as $rma) {
    /** @var Zolago_Rma_Model_Rma $rma */
    $owner = 0;
    if ($rma->getPo()->isPaymentCheckOnDelivery()) {
        $owner = Zolago_Payment_Model_Source_Channel_Owner::OWNER_VENDOR;// default settings
    } elseif ($rma->getPo()->isPaymentBanktransfer()) {
        $owner = Zolago_Payment_Model_Source_Channel_Owner::OWNER_MALL;// default settings
    } elseif ($rma->getPo()->isPaymentDotpay()) {
        $owner = Zolago_Payment_Model_Source_Channel_Owner::OWNER_MALL;// default settings
    }
    $rma->setData('payment_channel_owner', $owner);
    $rma->save();
}

$installer->endSetup();