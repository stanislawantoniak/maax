<?php
/**
 * Add info about gh statement rma process
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$tableName = $installer->getTable("urma/rma_item");

$installer->getConnection()->addColumn($tableName, "statement_id",
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'nullable'  => true,
        'comment'   => 'Statement Id'
    ));

$installer->endSetup();
