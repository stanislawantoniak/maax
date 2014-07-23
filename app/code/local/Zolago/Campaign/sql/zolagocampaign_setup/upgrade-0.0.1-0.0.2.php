<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();


/**
 * Change is_active to status
 */
 $table = $installer->getConnection()
    ->changeColumn($installer->getTable('zolagocampaign/campaign'), "is_active", "status", array(
		'type'		=> Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'nullable'  => false,
		'default'	=> 0
    ));
   
$installer->endSetup();
