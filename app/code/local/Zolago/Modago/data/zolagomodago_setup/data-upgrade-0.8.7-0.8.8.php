<?php

$allStoreViews = 0;

$sizeTableBlocks = array(
    array(
        'title' => 'Tabele rozmiarów - style (nie edytować)',
        'identifier' => 'sizetablecss',
        'content' => <<<EOD
body,html {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 13px;
	padding: 0;
	margin: 0;
}
body img {
	max-width: 100%;
}
div#sizetable-container {
	padding: 0 20px;
	min-width: 670px;
}
table.sizetable-table-container {
	width: 100%;
	border-collapse: collapse;
	margin-bottom: 25px;
	min-width: 650px;
}
table.sizetable-table-container:last-child {
	margin-bottom: 0;
}
table.sizetable-table-container td {
	vertical-align: top;
}
table.sizetable-table {
	width: 100%;
	border-collapse: collapse;
}
table.sizetable-table-scale-100 tr td.sizetable-td-container:nth-child(1) {
	width: 100%;
}
table.sizetable-table-scale-100 tr td.sizetable-td-container:nth-child(2) {
	width: 0;
}
table.sizetable-table-scale-90 tr td.sizetable-td-container:nth-child(1) {
	width: 90%;
}
table.sizetable-table-scale-90 tr td.sizetable-td-container:nth-child(2) {
	width: 10%;
}
table.sizetable-table-scale-80 tr td.sizetable-td-container:nth-child(1) {
	width: 80%;
}
table.sizetable-table-scale-80 tr td.sizetable-td-container:nth-child(2) {
	width: 20%;
}
table.sizetable-table-scale-70 tr td.sizetable-td-container:nth-child(1) {
	width: 70%;
}
table.sizetable-table-scale-70 tr td.sizetable-td-container:nth-child(2) {
	width: 30%;
}
table.sizetable-table-scale-60 tr td.sizetable-td-container:nth-child(1) {
	width: 60%;
}
table.sizetable-table-scale-60 tr td.sizetable-td-container:nth-child(2) {
	width: 40%;
}
table.sizetable-table-scale-50 tr td.sizetable-td-container:nth-child(1) {
	width: 50%;
}
table.sizetable-table-scale-50 tr td.sizetable-td-container:nth-child(2) {
	width: 50%;
}
table.sizetable-table-scale-40 tr td.sizetable-td-container:nth-child(1) {
	width: 40%;
}
table.sizetable-table-scale-40 tr td.sizetable-td-container:nth-child(2) {
	width: 60%;
}
table.sizetable-table-scale-30 tr td.sizetable-td-container:nth-child(1) {
	width: 30%;
}
table.sizetable-table-scale-30 tr td.sizetable-td-container:nth-child(2) {
	width: 70%;
}
table.sizetable-table-scale-20 tr td.sizetable-td-container:nth-child(1) {
	width: 20%;
}
table.sizetable-table-scale-20 tr td.sizetable-td-container:nth-child(2) {
	width: 80%;
}
table.sizetable-table-scale-10 tr td.sizetable-td-container:nth-child(1) {
	width: 10%;
}
table.sizetable-table-scale-10 tr td.sizetable-td-container:nth-child(2) {
	width: 90%;
}
table.sizetable-table tr td {
	border: 1px solid black;
	padding: 5px;
}
EOD
    ,
        'is_active' => 1,
        'stores' => $allStoreViews
    ),
    array(
        'title' => 'Tabela rozmiarów - kontener (nie edytować)',
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
    ),
    array(
        'title' => 'Tabela rozmiarów - Kontener 100%',
        'identifier' => 'sizetable-table-100',
        'content' => <<<EOD
<table class="sizetable-table-container sizetable-table-scale-100">
	<tr>
		<td class="sizetable-td-container"></td>
	</tr>
</table>
EOD
    ,
        'is_active' => 1,
        'stores' => $allStoreViews
    ),
    array(
        'title' => 'Tabela rozmiarów - Kontener 90%|10%',
        'identifier' => 'sizetable-table-90',
        'content' => <<<EOD
<table class="sizetable-table-container sizetable-table-scale-90">
	<tr>
		<td class="sizetable-td-container"></td>
		<td class="sizetable-td-container"></td>
	</tr>
</table>
EOD
    ,
        'is_active' => 1,
        'stores' => $allStoreViews
    ),
    array(
        'title' => 'Tabela rozmiarów - Kontener 80%|20%',
        'identifier' => 'sizetable-table-80',
        'content' => <<<EOD
<table class="sizetable-table-container sizetable-table-scale-80">
	<tr>
		<td class="sizetable-td-container"></td>
		<td class="sizetable-td-container"></td>
	</tr>
</table>
EOD
    ,
        'is_active' => 1,
        'stores' => $allStoreViews
    ),
    array(
        'title' => 'Tabela rozmiarów - Kontener 70%|30%',
        'identifier' => 'sizetable-table-70',
        'content' => <<<EOD
<table class="sizetable-table-container sizetable-table-scale-70">
	<tr>
		<td class="sizetable-td-container"></td>
		<td class="sizetable-td-container"></td>
	</tr>
</table>
EOD
    ,
        'is_active' => 1,
        'stores' => $allStoreViews
    ),
    array(
        'title' => 'Tabela rozmiarów - Kontener 60%|40%',
        'identifier' => 'sizetable-table-60',
        'content' => <<<EOD
<table class="sizetable-table-container sizetable-table-scale-60">
	<tr>
		<td class="sizetable-td-container"></td>
		<td class="sizetable-td-container"></td>
	</tr>
</table>
EOD
    ,
        'is_active' => 1,
        'stores' => $allStoreViews
    ),
    array(
        'title' => 'Tabela rozmiarów - Kontener 50%|50%',
        'identifier' => 'sizetable-table-50',
        'content' => <<<EOD
<table class="sizetable-table-container sizetable-table-scale-50">
	<tr>
		<td class="sizetable-td-container"></td>
		<td class="sizetable-td-container"></td>
	</tr>
</table>
EOD
    ,
        'is_active' => 1,
        'stores' => $allStoreViews
    ),
    array(
        'title' => 'Tabela rozmiarów - Kontener 40%|60%',
        'identifier' => 'sizetable-table-40',
        'content' => <<<EOD
<table class="sizetable-table-container sizetable-table-scale-40">
	<tr>
		<td class="sizetable-td-container"></td>
		<td class="sizetable-td-container"></td>
	</tr>
</table>
EOD
    ,
        'is_active' => 1,
        'stores' => $allStoreViews
    ),
    array(
        'title' => 'Tabela rozmiarów - Kontener 30%|70%',
        'identifier' => 'sizetable-table-30',
        'content' => <<<EOD
<table class="sizetable-table-container sizetable-table-scale-30">
	<tr>
		<td class="sizetable-td-container"></td>
		<td class="sizetable-td-container"></td>
	</tr>
</table>
EOD
    ,
        'is_active' => 1,
        'stores' => $allStoreViews
    ),
    array(
        'title' => 'Tabela rozmiarów - Kontener 20%|80%',
        'identifier' => 'sizetable-table-20',
        'content' => <<<EOD
<table class="sizetable-table-container sizetable-table-scale-20">
	<tr>
		<td class="sizetable-td-container"></td>
		<td class="sizetable-td-container"></td>
	</tr>
</table>
EOD
    ,
        'is_active' => 1,
        'stores' => $allStoreViews
    ),
    array(
        'title' => 'Tabela rozmiarów - Kontener 10%|90%',
        'identifier' => 'sizetable-table-10',
        'content' => <<<EOD
<table class="sizetable-table-container sizetable-table-scale-10">
	<tr>
		<td class="sizetable-td-container"></td>
		<td class="sizetable-td-container"></td>
	</tr>
</table>
EOD
    ,
        'is_active' => 1,
        'stores' => $allStoreViews
    ),
    array(
        'title' => 'Tabela rozmiarów - Tabela cała szerokość',
        'identifier' => 'sizetable-table-full',
        'content' => <<<EOD
<table class="sizetable-table">
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
</table>
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