<?php

$installer = new Mage_Eav_Model_Entity_Setup('core_setup');;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

/* Change renderer type in filter table */

$installer->getConnection()->modifyColumn(
		$installer->getTable("zolagocatalog/category_filter"), 
		"frontend_renderer", 
		array(
			"type" => Varien_Db_Ddl_Table::TYPE_TEXT,
			"length" => 255,
			"nullable" => true,
			"comment" => "Renderer"
		));

$installer->endSetup();