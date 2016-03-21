<?php
/* @var $this Mage_Core_Model_Resource_Setup */
$this->startSetup();


/*
Example:
      <name>WAW193</name>
      <type>Pack Machine</type>
      <postcode>03-543</postcode>
      <province>mazowieckie</province>
      <street>Barkocińska</street>
      <buildingnumber>6</buildingnumber>
      <town>Warszawa</town>
      <latitude>52.26876</latitude>
      <longitude>21.05663</longitude>
      <paymentavailable>t</paymentavailable>
      <status>Operating</status>
      <locationdescription><![CDATA[Przy markecie Biedronka]]></locationdescription>
      <locationDescription2><![CDATA[Biedronka]]></locationDescription2>
      <operatinghours><![CDATA[]]></operatinghours>
      <paymentpointdescr><![CDATA[Płatność kartą w paczkomacie lub PayByLink. Dostępność 24/7]]></paymentpointdescr>
      <partnerid>0</partnerid>
      <paymenttype>2</paymenttype>
*/
$tableName = $this->getTable("ghinpost/locker");
$table = $this->getConnection()

	->newTable($tableName)
	->addColumn("id", Varien_Db_Ddl_Table::TYPE_INTEGER, null,
		array(
			'identity' => true,
			'nullable' => false,
			'primary' => true
		))
	// Name
	->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 64,
		array(
			'nullable' => false
		))
	// Type Ex: Pack Machine
	->addColumn('type', Varien_Db_Ddl_Table::TYPE_TEXT, 64,
		array(
			'nullable' => true
		))
	// Postcode
	->addColumn('postcode', Varien_Db_Ddl_Table::TYPE_TEXT, 8,
		array(
			'nullable' => true
		))
	// Province
	->addColumn('province', Varien_Db_Ddl_Table::TYPE_TEXT, 256,
		array(
			'nullable' => true
		))
	// street
	->addColumn('street', Varien_Db_Ddl_Table::TYPE_TEXT, 256,
		array(
			'nullable' => true
		))
	// buildingnumber
	->addColumn('building_number', Varien_Db_Ddl_Table::TYPE_TEXT, 16,
		array(
			'nullable' => true
		))
	// town
	->addColumn('town', Varien_Db_Ddl_Table::TYPE_TEXT, 256,
		array(
			'nullable' => true
		))
	// latitude
	->addColumn('latitude', Varien_Db_Ddl_Table::TYPE_FLOAT, null,
		array(
			'nullable' => false
		))
	// longitude
	->addColumn('longitude', Varien_Db_Ddl_Table::TYPE_FLOAT, null,
		array(
			'nullable' => false
		))
	// paymentavailable
	->addColumn('payment_available', Varien_Db_Ddl_Table::TYPE_SMALLINT, 1,
		array(
			'nullable' => false
		))
	// operatinghours
	->addColumn('operating_hours', Varien_Db_Ddl_Table::TYPE_TEXT, 256,
		array(
			'nullable' => true
		))
	// locationdescription
	->addColumn('location_description', Varien_Db_Ddl_Table::TYPE_TEXT, 256,
		array(
			'nullable' => true
		))
	// locationDescription2
	->addColumn('location_description2', Varien_Db_Ddl_Table::TYPE_TEXT, 256,
		array(
			'nullable' => true
		))
	// paymentpointdescr
	->addColumn('payment_point_description', Varien_Db_Ddl_Table::TYPE_TEXT, 256,
		array(
			'nullable' => true
		))
	// partnerid
	->addColumn('partner_id', Varien_Db_Ddl_Table::TYPE_TEXT, 256,
		array(
			'nullable' => true
		))
	// paymenttype
	->addColumn('payment_type', Varien_Db_Ddl_Table::TYPE_SMALLINT, 1,
		array(
			'nullable' => false
		))
	// status
	->addColumn('status', Varien_Db_Ddl_Table::TYPE_TEXT, 64,
		array(
			'nullable' => false
		))
	->addIndex($this->getIdxName('ghinpost/locker', array('name')),
		array('name'));

$this->getConnection()->createTable($table);

$this->endSetup();