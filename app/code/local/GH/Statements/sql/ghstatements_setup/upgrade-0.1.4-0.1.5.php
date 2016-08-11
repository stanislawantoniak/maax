<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->getConnection()
    ->addColumn(
        $this->getTable('ghstatements/track'),
        "track_type",
        array(
            'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
            'nullable'  => true,
            'comment'   => 'Track type',
	        'default'   => null
        )
    );

$installer->getConnection()
	->addColumn(
		$this->getTable('sales/shipment_track'),
		"track_type",
		array(
			'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
			'nullable'  => true,
			'comment'   => 'Track type',
			'default'   => null
		)
	);

$installer->getConnection()
	->addColumn(
		$this->getTable('urma/rma_track'),
		"track_type",
		array(
			'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
			'nullable'  => true,
			'comment'   => 'Track type',
			'default'   => null
		)
	);

$installer->endSetup();
