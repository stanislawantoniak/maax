<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$table = $installer->getTable('zolagocampaign/campaign');

/**
 * Add STRIKEOUT_TYPE column
 */
$table = $installer->getConnection()
   ->addColumn($table, 'strikeout_type', array(
       'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
       'nullable' =>false,
       'default' => 1,
       'comment' => 'strikeout price type'
   ));

$installer->endSetup();
