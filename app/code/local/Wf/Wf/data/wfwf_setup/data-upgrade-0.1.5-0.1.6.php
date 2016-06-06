<?php

$blocks = array(
    array(
        'title' => 'WF: Featured products',
        'identifier' => 'wf_featured_products',
        'content'       =>
            <<<EOD
<div id="featured_products_tabs">
  <ul>
    <li><a href="#featured_products_tabs-1">Bestsellery</a></li>
    <li><a href="#featured_products_tabs-2">Nowości</a></li>
    <li><a href="#featured_products_tabs-3">Promocje</a></li>
  </ul>
  <div id="featured_products_tabs-1">{{widget type="productcarousel/productcarousel_widget_view" productcarousel_id="1"}} </div>
  <div id="featured_products_tabs-2">{{widget type="productcarousel/productcarousel_widget_view" productcarousel_id="2"}}</div>
  <div id="featured_products_tabs-3">{{widget type="productcarousel/productcarousel_widget_view" productcarousel_id="3"}}</div>
</div>
EOD
,
        'is_active'     => 1,
        'stores'        => 0
    ),
);

foreach ($blocks as $blockData) {
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

