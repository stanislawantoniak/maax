<?php
/* @var $this Mage_Core_Model_Resource_Setup */
$this->startSetup();


$installer = $this;

/**
 * Adding Extra Column to sales_flat_quote_address
 * to store the delivery instruction field
 */
$salesQuoteAddress = $installer->getTable('sales/quote_address');

$this->getConnection()
    ->addColumn($salesQuoteAddress,'inpost_locker_name',array(
        "type" => Varien_Db_Ddl_Table::TYPE_TEXT,
        "comment" => "Inpost Locker Name",
        "length" => "16",
        "nullable" => true,
        "default" => null
    ));


/**
 * Adding Extra Column to sales_flat_order_address
 * to store the delivery instruction field
 */
$salesOrder = $installer->getTable('sales/order');
$this->getConnection()
    ->addColumn($salesOrder,'inpost_locker_name',array(
    "type" => Varien_Db_Ddl_Table::TYPE_TEXT,
    "comment" => "Inpost Locker Name",
    "length" => "16",
    "nullable" => true,
    "default" => null
));

$this->endSetup();
