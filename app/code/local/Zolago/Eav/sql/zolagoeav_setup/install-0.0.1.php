<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();
$eavTable = $installer->getTable('catalog/eav_attribute');

Mage::log('Start Setup');
$installer->getConnection()->addColumn(
    $eavTable,
    'set_id',
	array(
		'type'		=> Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'nullable'  => true,
		'comment'	=> 'Default Set Id'
    )
);

$installer->getConnection()->addColumn(
    $eavTable,
    'is_mappable',
	array(
		'type'		=> Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'nullable'  => true,
		'comment'	=> 'Used in Mapping'
    )
);
Mage::log('End Setup');
$installer->endSetup();

