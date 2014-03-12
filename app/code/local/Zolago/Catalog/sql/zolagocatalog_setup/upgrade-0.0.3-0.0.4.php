<?php

$installer = new Mage_Core_Model_Resource_Setup('core_setup');;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();
$filterTable = $installer->getTable('zolagocatalog/category_filter');

/* Add "Can show more" Column */
$installer->getConnection()->addColumn(
	$filterTable,
	'can_show_more',
		array(
			'type'		=> Varien_Db_Ddl_Table::TYPE_INTEGER,
			'nullable'	=> false,
			'default'	=> 0,
			'comment'	=> 'Can Show More Flag'
		)
);

$installer->endSetup();