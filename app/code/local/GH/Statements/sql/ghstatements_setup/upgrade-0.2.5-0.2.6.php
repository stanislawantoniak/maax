<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->getConnection()
    ->addColumn(
        $this->getTable('ghstatements/rma'),
        "commission_return",
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'length' => '12,4',
            'comment' => 'Commission return in rma',
            'nullable' => false,
        )
    );

$installer->getConnection()
    ->addColumn(
        $this->getTable('ghstatements/rma'),
        "discount_return",
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'length' => '12,4',
            'comment' => 'Discount return in rma',
            'nullable' => false,
        )
    );

$installer->getConnection()
    ->addColumn(
        $this->getTable('ghstatements/statement'),
        "actual_balance",
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'length' => '12,4',
            'comment' => 'Last statement balance + payout value - sum of payments to vendor',
            'nullable' => false,
        )
    );

$installer->getConnection()
    ->addColumn(
        $this->getTable('ghstatements/statement'),
        "order_gallery_discount_value",
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'length' => '12,4',
            'comment' => 'Sum of discounts financed by gallery in orders',
            'nullable' => false,
        )
    );

$installer->getConnection()
    ->addColumn(
        $this->getTable('ghstatements/statement'),
        "rma_gallery_discount_value",
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'length' => '12,4',
            'comment' => 'Sum of discounts financed by gallery in rmas',
            'nullable' => false,
        )
    );

$installer->endSetup();

