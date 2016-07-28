<?php
// installation of navigation-dropdown-c- cms blocks
$cmsNavigationBlocks = array(
	array(
		'title'         => 'Category Top Small Banner',
		'identifier'    => 'category-top-banner',
		'content'       => <<<EOD
<style>
.category-top-green-box {
  margin-top: -40px;
  float: right;
  width: 400px;
  text-align: center;
  border: 1px solid green;
  padding: 4px;
}
@media (max-width: 809px) {
.category-top-green-box {
    display: none;
} }
</style>
<div class="category-top-green-box">
  DARMOWY PORADNIK - Sprawdź jak wybrać materac!
</div>
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

