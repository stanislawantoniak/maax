<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();
$eavTable = $installer->getTable('catalog/eav_attribute');

$installer->getConnection()->addColumn(
    $eavTable,
    'grid_permission',
	array(
		'type'		=> Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'nullable'  => true,
		'comment'	=> 'Grid Permission'
    )
);

$installer->endSetup();