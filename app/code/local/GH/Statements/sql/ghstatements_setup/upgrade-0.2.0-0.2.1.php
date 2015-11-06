<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->getConnection()
->addColumn(
    $this->getTable('ghstatements/statement'),
    "last_statement_balance",
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length'	=> '12,4',
        'comment'   => 'last statement balance',
        'nullable'   => false,
    )
);

$installer->endSetup();

