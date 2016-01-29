<?php

$allStoreViews = 0;

$sizeTableBlocks = array(
    array(
        'title' => 'Tabele rozmiarów - kontener (nie edytować)',
        'identifier' => 'sizetablecontainer',
        'content' => <<<EOD
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Tabela rozmiarów</title>
        <style>{{var sizetableCss}}</style>
    </head>
    <body>
        {{var sizetableContent}}
    </body>
</html>
EOD
    ,
        'is_active' => 1,
        'stores' => $allStoreViews
    ),
    array(
        'title' => 'Tabele rozmiarów - style (nie edytować)',
        'identifier' => 'sizetablecss',
        'content' => <<<EOD
table {
    width: 100%;
    min-width: 768px;
}
table td {
    border: 1px solid black;
    border-collapse: collapse;
}
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