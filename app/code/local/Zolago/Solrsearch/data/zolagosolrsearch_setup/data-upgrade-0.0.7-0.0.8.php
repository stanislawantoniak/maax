<?php

// installation privacy-settings-remember-me-description cms blocks
$cmsNavigationBlocks = array(
    array(
        'title'         => 'Empty search results',
        'identifier'    => 'search-page-empty',
        'content'       => <<<EOD
<br />
<p>
    Bardzo nam przykro.<br />
    Może zainteresują Cię inne popularne wyszukiwania w tej kategorii?
</p>
<br />
<p>
    <a href="#" style="text-decoration:underline">Staniki push-up</a><br /><br />
    <a href="#" style="text-decoration:underline">Bielizna Victoria's Secret</a><br /><br />
    <a href="#" style="text-decoration:underline">Majtki wyszczuplające</a><br /><br />
    <a href="#" style="text-decoration:underline">Biustonosze dla mam</a><br /><br />
</p>
EOD
    ,
        'is_active'     => 1,
        'stores'        => 0
    )
);

foreach ($cmsNavigationBlocks as $data) {
    $block = Mage::getModel('cms/block')->load($data['identifier']);
    if ($block->getBlockId()) {
        $oldData = $block->getData();
        $data = array_merge($oldData,$data);
    }
    $block->setData($data)->save();
}

