<?php
/**
 * Add flag 'have_specific_domain' into Admin Website Information edit form
 */

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$table = $installer->getTable('core/website');

$this->getConnection()
    ->addColumn($table, 'have_specific_domain', array(
        "type"      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        "unsigned"  => true,
        "nullable"  => false,
        "default"   => 0, // yes/no -> no -> 0
        "comment"   => "YES if website have specified domain, otherwise NO",
    ));

$installer->endSetup();
