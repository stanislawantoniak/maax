<?php


// installation content Brands page header

$brandsPageBlocks = array(
    array(
        'title' => 'Brands page header',
        'identifier' => 'brands-page-header',
        'content' =>
            <<<EOD
<div class="brands-cms wrapp-section bg-w">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12 brands-cms-title">
                <h1>
                    <span class="span-title uppercase" style="font-family: latoblack">Shops</span>
                </h1>
            </div>
        </div>
        <div class="row">
            <div class="brands-cms-content col-sm-12">
                <p style="font-size: 12px;">Shops header text</p>
            </div>
        </div>
    </div>
</div>
EOD
    ,
        'is_active' => 1,
        'stores' => 0
    ),
    array(
        'title' => 'Brands page block',
        'identifier' => 'brands-page-block',
        'content' =>
            <<<EOD
<div class="container-fluid bg-w">
	<div class="brands-page-cms-content">
		<h3>Brands page</h3>
		<p>Brands page text</p>
	</div>
</div>
EOD
    ,
        'is_active' => 1,
        'stores' => 0
    )

);

foreach ($brandsPageBlocks as $data) {
    $block = Mage::getModel('cms/block')->load($data['identifier']);
    if ($block->getBlockId()) {
        $oldData = $block->getData();
        $data = array_merge($oldData, $data);
    }

    $block->setData($data)->save();
}

