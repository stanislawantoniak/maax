<?php
/* @var $this Mage_Core_Model_Resource_Setup */
$this->startSetup();

$tableName = $this->getTable("ghinpost/locker");
	
$this->getConnection()->addColumn($tableName, 'is_active', array(
	"type"			=> Varien_Db_Ddl_Table::TYPE_SMALLINT,
	"nullable"		=> false,
	"default"		=> 0,
	"comment"		=> "Is active"
));

$this->getConnection()->addColumn($tableName, 'updated_at', array(
	"type"			=> Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
	"comment"		=> "Updated at"
));

$this->getConnection()->modifyColumn($tableName, 'name', array(
	'nullable'  => false,
	'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
	'comment'   => "InPost locker name",
	'length'    => 16
));


$this->endSetup();