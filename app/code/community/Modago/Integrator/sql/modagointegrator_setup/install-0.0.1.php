<?php

$installer = $this;
$installer->startSetup();
$table = $installer->getTable('sales/order');


$installer->getConnection()
    ->addColumn($table,'modago_order_id',array (
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable' => true,
        'lenght' => 55,
        'comment' => 'Order id from modago.pl gallery',
        
    ));

$installer->endSetup();