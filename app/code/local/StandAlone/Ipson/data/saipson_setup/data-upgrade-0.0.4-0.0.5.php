<?php
// installation of product cms blocks
$cmsNavigationBlocks = array(
	array(
		'title'         => 'Produkt - zakupy na raty',
		'identifier'    => 'product-installments',
		'content'       => <<<EOD
Zakupy na raty
EOD
	,
		'is_active'     => 1,
		'stores'        => 0
	),
	array(
		'title'         => 'Produkt - dofinansowanie',
		'identifier'    => 'product-funding',
		'content'       => <<<EOD
Dofinansowanie
EOD
	,
		'is_active'     => 1,
		'stores'        => 0
	)
);

foreach ($cmsNavigationBlocks as $blockData) {
	$collection = Mage::getModel('cms/block')->getCollection();
	$collection->addStoreFilter($blockData['stores']);
	$collection->addFieldToFilter('identifier',$blockData["identifier"]);
	$currentBlock = $collection->getFirstItem();

	if ($currentBlock->getBlockId()) {
		$oldBlock = $currentBlock->getData();
		$blockData = array_merge($oldBlock, $blockData);
	}
	$currentBlock->setData($blockData)->save();
}

