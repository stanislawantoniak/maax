<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$tableName = $installer->getTable('udropship/vendor');

// $installer->getConnection()->dropTable($installer->getTable('zolagoholidays/processingtime'));
// 
// $installer->getConnection()->changeColumn($tableName, 'max_shipping_date', 'max_shipping_days', array(
    // 'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
    // 'length' => 2,
    // 'comment' => 'Max shipping days'
// ));

$installer->getConnection()->addColumn($tableName, "max_shipping_time", array(
	"type"		=> Varien_Db_Ddl_Table::TYPE_TEXT,
	"length"	=> 8,
	"comment"	=> "Max shipping time"
));


$installer->endSetup();