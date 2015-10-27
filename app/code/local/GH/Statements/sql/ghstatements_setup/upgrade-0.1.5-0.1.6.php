<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/**
 * Saldo sprzedawcy
 */
$installer->getConnection()
    ->changeColumn(
        $this->getTable('ghstatements/vendor_balance'),
        "payment_to_client",
        "payment_from_client",
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => false,
            "length" => "12,4",
            'comment' => 'Płatności od klientów'
        )
    )
    ->modifyColumn(
        $this->getTable('ghstatements/vendor_balance'),
        "date",
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'nullable' => false,
            'comment' => 'Statement month (Miesiąc rozliczeniowy)'
        )
    );

$installer->endSetup();
