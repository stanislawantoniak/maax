<?php
/* @var $this Mage_Core_Model_Resource_Setup */
$this->startSetup();

$tableName = $this->getTable("ghinpost/locker");

$this->getConnection()->modifyColumn($tableName, 'latitude', array(
	'nullable'  => false,
	'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
	'length' => 32,
	'comment'   => "Latitude",
));

$this->getConnection()->modifyColumn($tableName, 'longitude', array(
	'nullable'  => false,
	'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
	'length' => 32,
	'comment'   => "Latitude",
));

$this->endSetup();
