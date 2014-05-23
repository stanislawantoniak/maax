<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$poTabel = $this->getTable("udpo/po");
$poItemTabel = $this->getTable("udpo/po_item");
$salesQuoteAddress = $this->getTable("sales/quote_address");
$salesOrderAddress = $this->getTable("sales/order_address");

/*******************************************************************************
 *  Need invoice
 *******************************************************************************/
$installer->getConnection()->addColumn($salesQuoteAddress, "need_invoice", array(
	"type" => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
	"comment" => "need_invoice",
	"default" => 0
));
$installer->getConnection()->addColumn($salesOrderAddress, "need_invoice", array(
	"type" => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
	"comment" => "need_invoice",
	"default" => 0
));

/*******************************************************************************
 *  PO Item update
 *******************************************************************************/



// Add Prices (price, base_price) incl tax
$installer->getConnection()->addColumn($poItemTabel, "price_incl_tax", array(
	"type" => Varien_Db_Ddl_Table::TYPE_DECIMAL,
	"comment" => "price_incl_tax",                
	'scale'     => 4,
    'precision' => 12,
));
$installer->getConnection()->addColumn($poItemTabel, "base_price_incl_tax", array(
	"type" => Varien_Db_Ddl_Table::TYPE_DECIMAL,
	"comment" => "base_price_incl_tax",           
	'scale'     => 4,
    'precision' => 12,
));

// Add discounts & discount precents
$installer->getConnection()->addColumn($poItemTabel, "discount_amount", array(
	"type" => Varien_Db_Ddl_Table::TYPE_DECIMAL,
	"comment" => "discount_amount",
	'scale'     => 4,
    'precision' => 12,
));
$installer->getConnection()->addColumn($poItemTabel, "discount_percent", array(
	"type" => Varien_Db_Ddl_Table::TYPE_DECIMAL,
	'scale'     => 4,
    'precision' => 12,
	"comment" => "discount_percent"
));

// Row prices
$installer->getConnection()->addColumn($poItemTabel, "row_total", array(
	"type" => Varien_Db_Ddl_Table::TYPE_DECIMAL,           
	'scale'     => 4,
    'precision' => 12,
	"comment" => "row_total"
));
$installer->getConnection()->addColumn($poItemTabel, "row_total_incl_tax", array(
	"type" => Varien_Db_Ddl_Table::TYPE_DECIMAL,           
	'scale'     => 4,
    'precision' => 12,
	"comment" => "row_total_incl_tax"
));
$installer->getConnection()->addColumn($poItemTabel, "base_row_total_incl_tax", array(
	"type" => Varien_Db_Ddl_Table::TYPE_DECIMAL,
	"comment" => "base_row_total_incl_tax",           
	'scale'     => 4,
    'precision' => 12,
));

// Add parent item id
$installer->getConnection()->addColumn($poItemTabel, "parent_item_id", array(
	"type" => Varien_Db_Ddl_Table::TYPE_INTEGER,
	"comment" => "parent_item_id",
	"nullable" => true
));
// Add index
$installer->getConnection()->addIndex(
		$poItemTabel, 
		$installer->getIdxName($poItemTabel, array("parent_item_id")), 
		array("parent_item_id")
);

$installer->endSetup();
