<?php

$allStoreViews = 0;

$sizeTableBlocks = array(
    array(
        'title' => 'Tabela rozmiarów - kontener',
        'identifier' => 'sizetablecontainer',
        'content' => <<<EOD
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Tabela rozmiarów</title>
        <style>{{var sizetableCss}}</style>
    </head>
    <body class="sizetable">
        <div id="sizetable-container">
            {{var sizetableContent}}
        </div>
    </body>
</html>
EOD
    ,
        'is_active' => 1,
        'stores' => $allStoreViews
    )
);


foreach ($sizeTableBlocks as $data) {
    $collection = Mage::getModel('cms/block')->getCollection();
    $collection->addStoreFilter($allStoreViews);
    $collection->addFieldToFilter("identifier", $data["identifier"]);
    $block = $collection->getFirstItem();

    if ($block->getBlockId()) {
        $oldData = $block->getData();
        $data = array_merge($oldData, $data);
    }
    $block->setData($data)->save();
}
unset($data);