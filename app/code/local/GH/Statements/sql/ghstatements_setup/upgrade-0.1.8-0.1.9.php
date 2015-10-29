<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->getConnection()
->addColumn(
    $this->getTable('ghstatements/statement'),
    "to_pay",
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length'	=> '12,4',
        'comment'   => 'Cash for vendor',
        'nullable'   => false,
    )
);
$installer->getConnection()
->addColumn(
    $this->getTable('ghstatements/statement'),
    "total_commission_netto",
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length'	=> '12,4',
        'comment'   => 'Modago commision netto value',
        'nullable'   => false,
    )
);
$installer->getConnection()
->addColumn(
    $this->getTable('ghstatements/statement'),
    "total_commission",
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length'	=> '12,4',
        'comment'   => 'Modago commision brutto value',
        'nullable'   => false,
    )
);

$installer->endSetup();

