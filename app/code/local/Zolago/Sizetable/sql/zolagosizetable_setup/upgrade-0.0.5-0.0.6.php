<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$sizeTableRule = $installer->getTable("zolagosizetable/sizetable_rule");
$sizeTable = $installer->getTable("zolagosizetable/sizetable");


/**
 * fk dla store_id
 */

$table = $installer->getConnection();

$table->modifyColumn($sizeTableRule, 'rule_id', array(
    'auto_increment' => true,
    'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'unsigned' => true,
    'nullable' => false
));

$installer->endSetup();