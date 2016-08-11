<?php
$cmsNavigationBlocks = array(
    array(
        'title'         => 'Pasek korzyści',
        'identifier'    => 'benefits-strip-modago',
        'content'       => <<<EOD
<div>
    benefits strip
</div>
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