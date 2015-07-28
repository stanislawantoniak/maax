<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

/**
 * Basic structure of statement
 */
$installer->getConnection()
    ->changeColumn(
        $this->getTable('ghstatements/statement'),
        "calendar_item_id",
        "event_date",
        array(
            'type'      => Varien_Db_Ddl_Table::TYPE_DATE,
            'nullable'  => false,
            'comment'   => 'Event date from statement calendar_item_id'
        )
    );

$installer->getConnection()
    ->addColumn(
        $this->getTable('ghstatements/order'),
        "qty",
        array(
            'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'nullable'  => false,
            'comment'   => 'Quantity'
        )
    );

$installer->endSetup();
