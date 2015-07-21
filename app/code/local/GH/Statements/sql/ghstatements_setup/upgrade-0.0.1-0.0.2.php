<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();


/**
 * Basic structure of statement
 */
 $table = $installer->getConnection()
    ->newTable($installer->getTable('ghstatements/statement'))
    ->addColumn("id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
	    'primary'   => true
    ),'Statemend Id')
	 ->addColumn("vendor_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		 'nullable'  => false
	 ),'Vendor Id')
	 ->addColumn("calendar_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		 'nullable'  => false
	 ),'Calendar_Id')
	 ->addColumn("calendar_item_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		 'nullable'  => false
	 ),"Calendar Item Id")
	 ->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 128, array(
        'nullable'  => false
        ), 'Name')
	 ->addColumn("order_commission_value", Varien_Db_Ddl_Table::TYPE_DECIMAL, "12,4", array(
		 'nullable'  => false
	 ), 'Order Commission Value')
	 ->addColumn("order_value", Varien_Db_Ddl_Table::TYPE_DECIMAL, "12,4", array(
		 'nullable'  => false
	 ),'Order Value')
	 ->addColumn("rma_commission_value", Varien_Db_Ddl_Table::TYPE_DECIMAL, "12,4", array(
		 'nullable'  => false
	 ),'RMA Commission Value')
	 ->addColumn("rma_value", Varien_Db_Ddl_Table::TYPE_DECIMAL, "12,4", array(
		 'nullable'  => false
	 ),'RMA Value')
	 ->addColumn("refund_value", Varien_Db_Ddl_Table::TYPE_DECIMAL, "12,4", array(
		 'nullable'  => false
	 ),'Refund Value')
	 ->addColumn("tracking_charge_subtotal", Varien_Db_Ddl_Table::TYPE_DECIMAL, "12,4", array(
		 'nullable'  => false
	 ),'Tracking Charge Subtotal (netto)')
	 ->addColumn("tracking_charge_total", Varien_Db_Ddl_Table::TYPE_DECIMAL, "12,4", array(
		 'nullable'  => false
	 ),'Tracking Charge Total');

$installer->getConnection()->createTable($table);

/**
 * basic structure of order statements
 */
$table = $installer->getConnection()
	->newTable($installer->getTable('ghstatements/order'))
	->addColumn("id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'identity'  => true,
		'nullable'  => false,
		'primary'   => true
	),'Order Statement Id')
	->addColumn("statement_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'nullable'  => false
	),'Statement Id')
	->addColumn("po_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'nullable'  => false
	),'PO Id')
	->addColumn("po_increment_id", Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
		'nullable'  => false
	),'PO Increment Id')
	->addColumn("po_item_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'nullable'  => false
	),'PO Item Id')
	->addColumn("sku", Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
		'nullable'  => false
	),'SKU')
	->addColumn("shipped_date", Varien_Db_Ddl_Table::TYPE_DATE, null, array(
		'nullable'  => false
	),'Shipped Date')
	->addColumn("carrier", Varien_Db_Ddl_Table::TYPE_VARCHAR, 32, array(
		'nullable'  => false
	),'Carrier')
	->addColumn("gallery_shipping_source", Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
		'nullable'  => false
	),'Gallery Shipping Source')
	->addColumn("payment_method", Varien_Db_Ddl_Table::TYPE_VARCHAR, 64, array(
		'nullable'  => false
	),'Payment Method')
	->addColumn("payment_channel_owner", Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
		'nullable'  => false
	),'Payment Channel Owner')
	->addColumn("price", Varien_Db_Ddl_Table::TYPE_DECIMAL, "12,4", array(
		'nullable'  => false
	),'Price')
	->addColumn("discount_amount", Varien_Db_Ddl_Table::TYPE_DECIMAL, "12,4", array(
		'nullable'  => false
	),'Discount Amount')
	->addColumn("final_price", Varien_Db_Ddl_Table::TYPE_DECIMAL, "12,4", array(
		'nullable'  => false
	),'Final Price')
	->addColumn("shipping_cost", Varien_Db_Ddl_Table::TYPE_DECIMAL, "12,4", array(
		'nullable'  => false
	),'Shipping Cost')
	->addColumn("commission_percent", Varien_Db_Ddl_Table::TYPE_DECIMAL, "12,4", array(
		'nullable'  => false
	),'Commission Percent')
	->addColumn("gallery_discount_value", Varien_Db_Ddl_Table::TYPE_DECIMAL, "12,4", array(
		'nullable'  => false
	),'Gallery Discount Value')
	->addColumn("commission_value", Varien_Db_Ddl_Table::TYPE_DECIMAL, "12,4", array(
		'nullable'  => false
	),'Commission Value')
	->addColumn("value", Varien_Db_Ddl_Table::TYPE_DECIMAL, "12,4", array(
		'nullable'  => false
	),'Value');

$installer->getConnection()->createTable($table);


/**
 * basic structure of refunds statements
 */
$table = $installer->getConnection()
	->newTable($installer->getTable('ghstatements/refund'))
	->addColumn("id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'identity'  => true,
		'nullable'  => false,
		'primary'   => true
	),'Refund Statement Id')
	->addColumn("statement_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'nullable'  => false
	),'Statement Id')
	->addColumn("po_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'nullable'  => false
	),'PO Id')
	->addColumn("po_increment_id", Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
		'nullable'  => false
	),'PO Increment Id')
	->addColumn("rma_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'nullable'  => false
	),'RMA Id')
	->addColumn("rma_increment_id", Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
		'nullable'  => false
	),'RMA Increment Id')
	->addColumn("date", Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
		'nullable'  => false
	),'Date')
	->addColumn("operator_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'nullable'  => true
	),'Operator Id')
	->addColumn("vendor_id", Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'nullable'  => false
	),'Vendor Id')
	->addColumn("value", Varien_Db_Ddl_Table::TYPE_DECIMAL, "12,4", array(
		'nullable'  => false
	),'Value');

$installer->getConnection()->createTable($table);


$installer->endSetup();
