<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

/**
 * Install statuses into DB
 */

$statuses = array(
	array(
		"code"					=>	Zolago_Rma_Model_Rma_Status::STATUS_PENDING_COURIER, 
		"title"					=>	"Pending courier booking", 
		"customer_notes"		=>	"",
		"edit_address"			=>	0,
		"print_shipping_label"	=>	0,
		"vendor_comment"		=>	1,
		"customer_comment"		=>	1,
		"notify_customer"		=>	1,
		"allow_resolution_notes"=>	0,
		"show_receiver"			=>	0,
		"sort_order"			=>	0
	),
	array(
		"code"					=>	Zolago_Rma_Model_Rma_Status::STATUS_PENDING_PICKUP, 
		"title"					=>	"Pending courier pick up", 
		"customer_notes"		=>	"",
		"edit_address"			=>	0,
		"print_shipping_label"	=>	0,
		"vendor_comment"		=>	1,
		"customer_comment"		=>	1,
		"notify_customer"		=>	1,
		"allow_resolution_notes"=>	0,
		"show_receiver"			=>	0,
		"sort_order"			=>	10
	),
	array(
		"code"					=>	Zolago_Rma_Model_Rma_Status::STATUS_PENDING_DELIVERY, 
		"title"					=>	"Shipped, pending delivery", 
		"customer_notes"		=>	"",
		"edit_address"			=>	0,
		"print_shipping_label"	=>	0,
		"vendor_comment"		=>	1,
		"customer_comment"		=>	1,
		"notify_customer"		=>	1,
		"allow_resolution_notes"=>	0,
		"show_receiver"			=>	0,
		"sort_order"			=>	20
	),
	array(
		"code"					=>	Zolago_Rma_Model_Rma_Status::STATUS_PENDING, 
		"title"					=>	"Pending", 
		"customer_notes"		=>	"",
		"edit_address"			=>	0,
		"print_shipping_label"	=>	0,
		"vendor_comment"		=>	1,
		"customer_comment"		=>	1,
		"notify_customer"		=>	1,
		"allow_resolution_notes"=>	0,
		"show_receiver"			=>	0,
		"sort_order"			=>	30
	),
	array(
		"code"					=>	Zolago_Rma_Model_Rma_Status::STATUS_SHIPMENT_RECIVED, 
		"title"					=>	"Shipment received/delivered", 
		"customer_notes"		=>	"",
		"edit_address"			=>	1,
		"print_shipping_label"	=>	0,
		"vendor_comment"		=>	1,
		"customer_comment"		=>	1,
		"notify_customer"		=>	1,
		"allow_resolution_notes"=>	0,
		"show_receiver"			=>	0,
		"sort_order"			=>	40
	),
	array(
		"code"					=>	Zolago_Rma_Model_Rma_Status::STATUS_PROCESSING, 
		"title"					=>	"Pending resolution", 
		"customer_notes"		=>	"",
		"edit_address"			=>	1,
		"print_shipping_label"	=>	1,
		"vendor_comment"		=>	1,
		"customer_comment"		=>	1,
		"notify_customer"		=>	1,
		"allow_resolution_notes"=>	0,
		"show_receiver"			=>	0,
		"sort_order"			=>	50
	),
	array(
		"code"					=>	Zolago_Rma_Model_Rma_Status::STATUS_ACCEPTED, 
		"title"					=>	"Accepted", 
		"customer_notes"		=>	"",
		"edit_address"			=>	1,
		"print_shipping_label"	=>	1,
		"vendor_comment"		=>	1,
		"customer_comment"		=>	1,
		"notify_customer"		=>	1,
		"allow_resolution_notes"=>	0,
		"show_receiver"			=>	0,
		"sort_order"			=>	60
	),
	array(
		"code"					=>	Zolago_Rma_Model_Rma_Status::STATUS_REJECTED, 
		"title"					=>	"Rejected", 
		"customer_notes"		=>	"",
		"edit_address"			=>	1,
		"print_shipping_label"	=>	1,
		"vendor_comment"		=>	1,
		"customer_comment"		=>	1,
		"notify_customer"		=>	1,
		"allow_resolution_notes"=>	0,
		"show_receiver"			=>	0,
		"sort_order"			=>	70
	),
	array(
		"code"					=>	Zolago_Rma_Model_Rma_Status::STATUS_CLOSED_ACCEPTED, 
		"title"					=>	"Closed - accepted", 
		"customer_notes"		=>	"",
		"edit_address"			=>	0,
		"print_shipping_label"	=>	0,
		"vendor_comment"		=>	1,
		"customer_comment"		=>	0,
		"notify_customer"		=>	1,
		"allow_resolution_notes"=>	0,
		"show_receiver"			=>	0,
		"sort_order"			=>	80
	),
	array(
		"code"					=>	Zolago_Rma_Model_Rma_Status::STATUS_CLOSED_REJECTED, 
		"title"					=>	"Closed - rejected", 
		"customer_notes"		=>	"",
		"edit_address"			=>	0,
		"print_shipping_label"	=>	0,
		"vendor_comment"		=>	1,
		"customer_comment"		=>	0,
		"notify_customer"		=>	1,
		"allow_resolution_notes"=>	0,
		"show_receiver"			=>	0,
		"sort_order"			=>	90
	),
);

$installer->getConnection()->insertOnDuplicate($installer->getTable('core/config_data'), array(
	"scope"		=>	"default",
	"scope_id"	=>	0,
	"path"		=>	"urma/general/statuses",
	"value"		=>	Mage::helper('udropship')->serialize($statuses)
));

