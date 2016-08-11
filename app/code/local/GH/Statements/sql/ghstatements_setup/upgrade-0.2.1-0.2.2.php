<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/**
 * Saldo sprzedawcy
 */
$table = $this->getTable('ghstatements/vendor_balance');

$installer->getConnection()
    ->dropColumn(
        $table,
        "payment_from_client_completed"
    );

$installer->endSetup();
