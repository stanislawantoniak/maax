<?php
//remove old blocks (just to be sure that everything is set up right
$blocksToRemove = Mage::getModel('cms/block')->getCollection();
$blocksToRemove->addFieldToFilter("identifier", array('in' => array("navigation-main-mobile")));
foreach($blocksToRemove as $blockToRemove) {
	$blockToRemove->delete();
}


$blocksToCreate = array(
	array(
		'title' => 'Nawigacja główna - mobile',
		'identifier' => 'navigation-main-mobile',
		'content' =>
			<<<EOD
<ul id="nav_mobile" class="navigation visible-xs" role="navigation">
	{{block type='zolagomodago/catalog_category' name='page.header.bottom.category.items.mobile' template='page/html/header/bottom.category.items.mobile.phtml'}}
		<li><a class="clickable" href="/mypromotions" >SALE</a></li>
		<li><a class="clickable" href="/lookbook" data-catids="lookbook">LOOK BOOK</a></li>
		<li><a class="clickable" href="/storesmap" data-catids="lookbook">ZNAJDŹ SKLEP</a></li>
</ul>
EOD
	,
		'is_active' => 1,
		'stores' => 0
	)
);

foreach ($blocksToCreate as $data) {
	$block = Mage::getModel('cms/block')->load($data['identifier']);
	if ($block->getBlockId()) {
		$oldData = $block->getData();
		$data = array_merge($oldData,$data);
	}

	$block->setData($data)->save();
}