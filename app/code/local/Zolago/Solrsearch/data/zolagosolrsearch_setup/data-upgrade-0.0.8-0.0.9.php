<?php

// installation privacy-settings-remember-me-description cms blocks
$cmsNavigationBlocks = array(
    array(
        'title'         => 'Empty search results',
        'identifier'    => 'search-page-empty',
        'content'       => <<<EOD
<p>
    Bardzo nam przykro.<br />
    Może zainteresują Cię inne popularne wyszukiwania w tej kategorii?
</p>
<ul>
    <li><a href="{{store url='#'}}">Staniki push-up (link do aktualnego kontekstu)</a></li>
    <li><a href="{{store url='#' _no_vendor='1'}}">Bielizna Victoria's Secret (link do kontekstu galerii)</a></li>
    <li><a href="{{store url='#' _no_vendor='1'}}">Majtki wyszczuplające (link do kontekstu galerii)</a></li>
    <li><a href="{{store url='#'}}">Biustonosze dla mam (link do aktualnego kontekstu)</a></li>
</ul>
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

