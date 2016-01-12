<?php

$installer = $this;
$installer->startSetup();

$marketingCostTypes = array(
	array('name' => 'Nokaut',            'code' => 'nokaut'),
	array('name' => 'Okazje.info',       'code' => 'okazje_info'),
	array('name' => 'Skąpiec',           'code' => 'skapiec'),
	array('name' => 'Idealo',            'code' => 'idealo'),
	array('name' => 'Adwords',           'code' => 'adwords'),
	array('name' => 'AffiliateNetwork1', 'code' => 'AN1'),
	array('name' => 'AffiliateNetwork2', 'code' => 'AN2'),
);

foreach ($marketingCostTypes as $typeData) {
	/** @var GH_Marketing_Model_Marketing_Cost_Type $marketingCostType */
	$marketingCostType = Mage::getModel('ghmarketing/marketing_cost_type');
	$marketingCostType->addData($typeData);
	$marketingCostType->save();
}

$installer->endSetup();