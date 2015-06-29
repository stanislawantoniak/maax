<?php
/**
 * Add info about how money flow (through mall or vendor)
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$tableName = $installer->getTable("urma/rma");

$installer->getConnection()->addColumn($tableName, 'payment_channel_owner', array(
    "type" => Varien_Db_Ddl_Table::TYPE_SMALLINT,
    "comment" => "Payment channel owner",
    "nullable" => false,
));

$installer->endSetup();
