<?php
/**
 * Add 'attribute_base_store' into Admin Store View Information edit form
 * If attribute_base_store will be specified labels will be taken if not present.
 * If you do not specify a store view then the default (Admin) labels will be used
 */

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$table = $installer->getTable('core/store');

$this->getConnection()
    ->addColumn($table, 'virtual_root_category', array(
        "type"      => Varien_Db_Ddl_Table::TYPE_TEXT,
        "nullable"  => true,
        "comment"   => "Name of virtual category for hamburger menu",
        "length"    => 255,
    ));

$installer->endSetup();
