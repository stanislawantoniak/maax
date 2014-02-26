<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$operatorTable = $installer->getTable("zolagooperator/operator");
$operatorPosTable = $installer->getTable("zolagooperator/operator_pos");

/**
 * operator table
 */

$table = $installer->getConnection()
    ->newTable($operatorTable)
    ->addColumn("operator_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
    ))
        
    // Struct
    ->addColumn('vendor_id',  Varien_Db_Ddl_Table::TYPE_INTEGER)
    ->addColumn('is_active',        Varien_Db_Ddl_Table::TYPE_INTEGER, 1, array('default'=>0, 'nullable' => false))
    ->addColumn('email',            Varien_Db_Ddl_Table::TYPE_TEXT, 100, array('nullable' => false))
    ->addColumn('password',            Varien_Db_Ddl_Table::TYPE_TEXT, 100, array('nullable' => false))
    ->addColumn("firstname",             Varien_Db_Ddl_Table::TYPE_TEXT, 100 )
    ->addColumn("lastname",             Varien_Db_Ddl_Table::TYPE_TEXT, 100 )
    ->addColumn('phone',            Varien_Db_Ddl_Table::TYPE_TEXT, 50)
                    
    // Misc
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'Creation Time')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'Update Time')
        
    // Indexes
    ->addIndex($installer->getIdxName('zolagooperator/operator', array('vendor_id')),
        array('vendor_id'))
    ->addIndex($installer->getIdxName('zolagooperator/operator', array('is_active')),
        array('is_active'))
    ->addIndex($installer->getIdxName('zolagooperator/operator', array('email')),
        array('email'),Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
    
    // Foreign Keys
    ->addForeignKey(
        $installer->getFkName('zolagooperator/operator', 'vendor_id', 'udropship/vendor', 'vendor_id'),
        'vendor_id', $installer->getTable('udropship/vendor'), 'vendor_id',
         Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE
        );

$installer->getConnection()->createTable($table);

/**
 * Operator - POS relation
 */

$table = $installer->getConnection()
    ->newTable($operatorPosTable)

    // Struct
    ->addColumn("operator_id",    Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('nullable' => false))
    ->addColumn("pos_id",       Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('nullable' => false))
       
    // Indexes
    ->addIndex($installer->getIdxName('zolagooperator/operator_pos', array('operator_id', 'pos_id')),
        array('operator_id', 'pos_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
    ->addIndex($installer->getIdxName('zolagooperator/operator_pos', array('operator_id')),
        array('operator_id'))
    ->addIndex($installer->getIdxName('zolagooperator/operator_pos', array('pos_id')),
        array('pos_id'))
    
    // Foreign Keys
    ->addForeignKey(
        $installer->getFkName('zolagooperator/operator_pos', 'operator_id', 'zolagooperator/operator', 'operator_id'),
        'operator_id', $installer->getTable('zolagooperator/operator'), 'operator_id', 
         Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
     )
     ->addForeignKey(
        $installer->getFkName('zolagooperator/operator_pos', 'pos_id', 'zolagopos/pos', 'pos_id'),
        'pos_id', $installer->getTable('zolagopos/pos'), 'pos_id', 
         Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
     );
$installer->getConnection()->createTable($table);

$installer->endSetup();
