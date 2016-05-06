<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$tableRma = $this->getTable("ghstatements/rma");
$installer->getConnection()
	->addColumn(
		$tableRma,
		"charge_commission_flag",
		array(
			'type'      => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
			'nullable'  => false,
			'comment'   => 'Charge a commission in statements?',
			'default'   => true,
		)
	);

$installer->endSetup();
