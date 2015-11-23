<?php
//remove old blocks (just to be sure that everything is set up right
$blocksToRemove = Mage::getModel('cms/block')->getCollection();
$blocksToRemove->addFieldToFilter("identifier", array('in' => array("navigation-main-desktop","navigation-main-mobile")));
foreach($blocksToRemove as $blockToRemove) {
	$blockToRemove->delete();
}

//recreate blocks with correct scopes
$allStores = 0;
$modagoStore =  Mage::app()->getStore('default')->getId();

$blocksToCreate = array(
	array(
		'title' => 'Nawigacja główna - desktop (Modago.pl)',
		'identifier' => 'navigation-main-desktop',
		'content' =>
			<<<EOD
<ul id="nav_desc" class="navigation hidden-xs" role="navigation">
	{{block type='zolagomodago/catalog_category' name='page.header.bottom.category.items' template='page/html/header/bottom.category.items.phtml'}}
	<li><a class="clickable" href="{{store direct_url='modago/brands' _no_vendor='1'}}" data-catids="modago/brands/index">Marki</a></li>
	<li><a class="clickable" href="{{store direct_url='mypromotions' _no_vendor='1'}}" data-catids="mypromotions">Moje promocje</a></li>
</ul>
EOD
	,
		'is_active' => 1,
		'stores' => $modagoStore
	),
	array(
		'title' => 'Nawigacja główna - desktop (default)',
		'identifier' => 'navigation-main-desktop',
		'content' =>
			<<<EOD
<ul id="nav_desc" class="navigation hidden-xs" role="navigation">
	{{block type='zolagomodago/catalog_category' name='page.header.bottom.category.items' template='page/html/header/bottom.category.items.phtml'}}
</ul>
EOD
	,
		'is_active' => 1,
		'stores' => $allStores
	),
	array(
		'title' => 'Nawigacja główna - mobile (Modago.pl)',
		'identifier' => 'navigation-main-mobile',
		'content' =>
			<<<EOD
<ul id="nav_mobile" class="navigation visible-xs" role="navigation">
	{{block type='zolagomodago/catalog_category' name='page.header.bottom.category.items.mobile' template='page/html/header/bottom.category.items.mobile.phtml'}}
	<li><a class="clickable" href="{{store direct_url='modago/brands' _no_vendor='1'}}">Marki <i class="fa fa-chevron-right"></i></a></li>
</ul>
EOD
	,
		'is_active' => 1,
		'stores' => $modagoStore
	),
	array(
		'title' => 'Nawigacja główna - mobile (default)',
		'identifier' => 'navigation-main-mobile',
		'content' =>
			<<<EOD
<ul id="nav_mobile" class="navigation visible-xs" role="navigation">
	{{block type='zolagomodago/catalog_category' name='page.header.bottom.category.items.mobile' template='page/html/header/bottom.category.items.mobile.phtml'}}
</ul>
EOD
	,
		'is_active' => 1,
		'stores' => $allStores
	)
);

foreach ($blocksToCreate as $blockData) {
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