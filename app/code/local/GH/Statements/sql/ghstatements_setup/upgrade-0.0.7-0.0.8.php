<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->getConnection()
    ->addForeignKey(
        $installer->getFkName('ghstatements/order', 'statement_id', 'ghstatements/statement', 'id'), //$fkName
        $installer->getTable('ghstatements/order'), //$tableName
        'statement_id', //$columnName
        $installer->getTable('ghstatements/statement'), //$refTableName
        'id', //$refColumnName
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    );

$installer->getConnection()
    ->addForeignKey(
        $installer->getFkName('ghstatements/track', 'statement_id', 'ghstatements/statement', 'id'), //$fkName
        $installer->getTable('ghstatements/track'), //$tableName
        'statement_id', //$columnName
        $installer->getTable('ghstatements/statement'), //$refTableName
        'id', //$refColumnName
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    );


$installer->endSetup();
