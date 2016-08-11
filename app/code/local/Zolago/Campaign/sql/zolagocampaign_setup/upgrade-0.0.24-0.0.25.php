<?php
/**
 * Add field 'vendor_owner' into Admin Website Information edit form
 */

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$table = $installer->getTable('core/website');

$this->getConnection()
    ->addColumn($table, 'vendor_id', array(
        "type"      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        //"unsigned"  => true,
        //"nullable"  => false,
        "default"   => NULL,
        "comment"   => "Website vendor owner",
    ));

// Add foreign key constraint
$installer->getConnection()
    ->addForeignKey(
        $installer->getFkName(
            'core/website', 'vendor_id',
            'udropship/vendor', 'vendor_id'
        ),
        $table,
        'vendor_id',
        $installer->getTable('udropship/vendor'),
        'vendor_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL,
        Varien_Db_Ddl_Table::ACTION_SET_NULL
    );

$installer->endSetup();
