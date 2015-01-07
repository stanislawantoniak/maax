<?php
$installer = $this;
$connection = $installer->getConnection();

$installer->startSetup();

$installer->getConnection()
    ->addColumn('newsletter_subscriber',
        'coupon_id',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'nullable' => true,
            'default' => null,
            'comment' => 'id of coupon sent after subscribing'
        )
    );

$installer->endSetup();