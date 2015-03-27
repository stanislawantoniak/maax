<?php

/**
 * Install of cms help block for GH API
 */

$cmsGhapiHelp = array(
    array(
        'title'         => 'Help for GH API',
        'identifier'    => 'ghapi-help',
        'content'       => <<<EOD
<p>
    HELP - CMS BLOCK
</p>

EOD
    ,
        'is_active'     => 1,
        'stores'        => 0
    )
);

foreach ($cmsGhapiHelp as $data) {
    $block = Mage::getModel('cms/block')->load($data['identifier']);
    if ($block->getBlockId()) {
        $oldData = $block->getData();
        $data = array_merge($oldData,$data);
    }
    $block->setData($data)->save();
}

