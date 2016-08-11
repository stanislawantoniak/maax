<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->getConnection()
->addColumn(
    $this->getTable('ghstatements/statement'),
    "gallery_discount_value",
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length'	=> '12,4',
        'comment'   => 'Gallery discount value',
        'nullable'   => false,
    )
);
$installer->getConnection()
->addColumn(
    $this->getTable('ghstatements/statement'),
    "commission_correction",
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length'	=> '12,4',
        'comment'   => 'Commission correction',
        'nullable'   => false,
    )
);
$installer->getConnection()
->addColumn(
    $this->getTable('ghstatements/statement'),
    "delivery_correction",
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length'	=> '12,4',
        'comment'   => 'Delivery correction',
        'nullable'   => false,
    )
);
$installer->getConnection()
->addColumn(
    $this->getTable('ghstatements/statement'),
    "marketing_correction",
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length'	=> '12,4',
        'comment'   => 'Marketing correction',
        'nullable'   => false,
    )
);
$installer->getConnection()
    ->addColumn(
        $this->getTable('zolagopayment/vendor_invoice'),
        "statement_id",
        array(
            'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'comment'   => 'Statement connection',
            'nullable'   => true,
        )
    );
$installer->getConnection()
    ->addIndex(
            $this->getTable('zolagopayment/vendor_invoice'),
            $installer->getIdxName('zolagopayment/vendor_invoice', array('statement_id')),
            array('statement_id')
        );

$installer->getConnection()
    ->addForeignKey(
        $installer->getFkName('zolagopayment/vendor_invoice', 'statement_id','ghstatements/statement', 'id'),
        $this->getTable('zolagopayment/vendor_invoice'),'statement_id',        
        $this->getTable('ghstatements/statement'),'id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE
    );


$installer->endSetup();
