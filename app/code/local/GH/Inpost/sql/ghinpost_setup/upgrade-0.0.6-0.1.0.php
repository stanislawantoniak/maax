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
    ->changeColumn(
        $salesQuoteAddress,
        'inpost_locker_name', //old name
        'delivery_point_name', //new name
        array(
            "type" => Varien_Db_Ddl_Table::TYPE_TEXT,
            "comment" => "Delivery Point Name",
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
    ->changeColumn(
        $salesOrder,
        'inpost_locker_name', //old name
        'delivery_point_name', //new name
        array(
            "type" => Varien_Db_Ddl_Table::TYPE_TEXT,
            "comment" => "Delivery Point Name",
            "length" => "16",
            "nullable" => true,
            "default" => null
        ));

/**
 * Adding Extra Column to udpo_po
 * to store the delivery instruction field
 */
$po = $this->getTable('udpo/po');
$this->getConnection()->changeColumn(
    $po,
    'inpost_locker_name', //old name
    'delivery_point_name', //new name
    array(
        "type" => Varien_Db_Ddl_Table::TYPE_TEXT,
        "comment" => "Delivery Point Name",
        "length" => "16", //currently max length is 8 signs - setting up 16 to ensure compatibility in the future
        "nullable" => true,
        "default" => null
    ));

$this->endSetup();
