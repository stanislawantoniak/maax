<?php
/**
 * update empty method
 */

$query = 'UPDATE %s as main_table INNER JOIN %s as payment ON main_table.payment_id = payment.entity_id SET main_table.payment_method = payment.method WHERE main_table.payment_method = ""';
$resource = Mage::getSingleton('core/resource');
$connection = $resource->getConnection('core_write');
$tableTransaction = $resource->getTableName('sales/payment_transaction');
$tablePayment = $resource->getTableName('sales/order_payment');

$connection->query(sprintf($query,$tableTransaction,$tablePayment));