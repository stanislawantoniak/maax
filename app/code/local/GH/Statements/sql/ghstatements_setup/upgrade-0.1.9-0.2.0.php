<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/**
 * Saldo sprzedawcy
 */
$table = $this->getTable('ghstatements/vendor_balance');

$installer->getConnection()->addColumn($table,
    "payment_from_client_completed",
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'nullable' => true,
        'comment' => 'Płatności od klientów (PO wyslane, zrealizowane)'
    ));

$installer->endSetup();
