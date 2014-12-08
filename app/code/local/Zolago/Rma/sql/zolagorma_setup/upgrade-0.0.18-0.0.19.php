<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$tableName = $installer->getTable("urma/rma");

if(!$installer->getConnection()->tableColumnExists($tableName, 'new_customer_question')){
    $installer->getConnection()->addColumn($tableName, 'new_customer_question', array(
        "type" => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'unsigned' => true,
        'default'   => '0',
        "comment" => "New Customer Question"
    ));
}


$installer->endSetup();

