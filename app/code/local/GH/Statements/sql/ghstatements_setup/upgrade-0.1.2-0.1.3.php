<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();


/**
 * Structure of gh_statements_rma
 */
$installer->getConnection()
    ->addColumn(
        $installer->getTable('ghstatements/rma'),
        "approved_refund_amount",
        array(
            'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            "length"	=> "12,4",
            'nullable'  => false,
            'comment'   => 'Approved refund amount'
        ));

$installer->getConnection()
    ->dropColumn(
        $installer->getTable('ghstatements/rma'),
        "shipping_cost"
    );

$installer->endSetup();
