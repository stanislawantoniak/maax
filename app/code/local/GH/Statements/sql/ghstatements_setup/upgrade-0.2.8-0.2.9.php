<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$table = $this->getTable('ghstatements/vendor_balance');

$installer->getConnection()
    ->modifyColumn(
        $table,
        'status',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
            'nullable' => false,
            'comment' => 'Status miesiąca',
            'length' => '1',
            'default' => 0
        )
    );


$installer->getConnection()
    ->modifyColumn(
        $table,
        "payment_from_client",
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => false,
            "length" => "12,4",
            'default' => 0,
            'comment' => 'Płatności od klientów'

        )
    );

$installer->getConnection()
    ->modifyColumn(
        $table,
        "payment_return_to_client",
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => false,
            "length" => "12,4",
            'default' => 0,
            'comment' => 'Zwroty płatności do klientów'
        )
    );
$installer->getConnection()
    ->modifyColumn(
        $table,
        "vendor_payment_cost",
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => false,
            "length" => "12,4",
            'default' => 0,
            'comment' => 'Wypłaty'
        )
    );
$installer->getConnection()
    ->modifyColumn(
        $table,
        "vendor_invoice_cost",
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => false,
            "length" => "12,4",
            'default' => 0,
            'comment' => 'Faktury i korekty faktur'
        )
    );
$installer->getConnection()
    ->modifyColumn(
        $table,
        "balance_per_month",
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => false,
            "length" => "12,4",
            'default' => 0,
            'comment' => 'Bilans miesiąca'
        )
    );
$installer->getConnection()
    ->modifyColumn(
        $table,
        "balance_cumulative",
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => false,
            "length" => "12,4",
            'default' => 0,
            'comment' => 'Saldo narastająco'
        )
    );
$installer->getConnection()
    ->modifyColumn(
        $table,
        "balance_due",
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable' => false,
            "length" => "12,4",
            'default' => 0,
            'comment' => 'Saldo wymagalne'
        )
    );

$installer->endSetup();

