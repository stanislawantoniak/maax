<?php
/**
 * Add flag 'vendor_sites_allowed', 'is_preview_website','website_login','website_password'
 */

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$table = $installer->getTable('core/website');

$this->getConnection()
    ->addColumn($table, 'vendor_sites_allowed', array(
        "type"      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        "unsigned"  => true,
        "nullable"  => false,
        "default"   => 0, // yes/no -> no -> 0
        "comment"   => "YES if website can show vendor shops, otherwise NO",
    ));
$this->getConnection()
    ->addColumn($table, 'is_preview_website', array(
        "type"      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        "unsigned"  => true,
        "nullable"  => false,
        "default"   => 0, // yes/no -> no -> 0
        "comment"   => "YES if website is for preview, otherwise NO",
    ));
$this->getConnection()
    ->addColumn($table, 'website_login', array(
        "type"      => Varien_Db_Ddl_Table::TYPE_TEXT,
        "length"	=> 32,
        "nullable"  => true,
        "comment"   => "Login for website preview access",
    ));
$this->getConnection()
    ->addColumn($table, 'website_password', array(
        "type"      => Varien_Db_Ddl_Table::TYPE_TEXT,
        "nullable"  => true,
        "length"	=> 32,
        "comment"   => "Password for website preview access",
    ));

$installer->endSetup();
