<?php
/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();


$installer->getConnection()
    ->addColumn(
        $installer->getTable('zolagopayment/vendor_payment'),
        'statement_id',
        array(
            'nullable'  => true,
            'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'comment'   => "Statement Id",
            'default'   => null,
        )
    );

$installer->getConnection()
    ->addForeignKey(
        $installer->getFkName('zolagopayment/vendor_payment', 'statement_id', 'ghstatements/statement', 'id'), //$fkName
        $installer->getTable('zolagopayment/vendor_payment'), //$tableName
        'statement_id', //$columnName
        $installer->getTable('ghstatements/statement'), //$refTableName
        'id', //$refColumnName
        Varien_Db_Ddl_Table::ACTION_SET_NULL,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    );

$installer->getConnection()
    ->addColumn(
        $this->getTable('ghstatements/statement'),
        "payment_value",
        array(
            'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'nullable'  => false,
            'comment'   => 'Payment Value',
            'length'      => '12,4'
        )
    );

$installer->endSetup();