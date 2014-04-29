<?php

$installer = new Mage_Catalog_Model_Resource_Setup('core_setup');
/* @var $installer Mage_Catalog_Model_Resource_Setup */

 $table = $installer->getConnection()
    ->addColumn(
			$installer->getTable('zolagocatalog/category_filter'), 
			"is_rolled",
			array(
				'type'		=> Varien_Db_Ddl_Table::TYPE_BOOLEAN,
				'default'	=> 0,
				'comment'	=> "Is rolled"
			)
	);

$installer->endSetup();




