<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();


/**
 * Basic structure of provider
 */
 $table = $installer->getConnection()
    ->addColumn($installer->getTable('zolagopayment/provider'), "type", array(
		"type"		=> Varien_Db_Ddl_Table::TYPE_TEXT,
		"length"	=> 32,
        'nullable'	=> false,
		'comment'	=> "Model type"
	));
 
$installer->endSetup();
