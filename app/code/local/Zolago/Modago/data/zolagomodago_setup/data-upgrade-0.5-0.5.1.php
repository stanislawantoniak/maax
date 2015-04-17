<?php

// installation of bottom header cms blocks
$cmsNavigationBlocks = array(

    array(
        'title'         => 'Top bottom header mobile vendor Esotiq new',
        'identifier'    => 'top-bottom-header-mobile-v-4',
        'content'       => <<<EOD
<div class="container-fluid vendor-top-bottom-header action-box-bundle clearfix">
<div class="col-md-6 col-sm-4 logo-part">
{{block name="vendor.logo"  type="zolagoudmspro/vendor_logo" template="unirgy/microsite/vendor.logo.phtml"}}
</div>

</div>
EOD
    ,
        'is_active'     => 1,
        'stores'        => 0
    ),
    array(
        'title'         => 'Top bottom header mobile vendor Matterhorn',
        'identifier'    => 'top-bottom-header-mobile-v-5',
        'content'       => <<<EOD
<div class="container-fluid vendor-top-bottom-header action-box-bundle clearfix">
<div class="col-md-6 col-sm-4 logo-part">
{{block name="vendor.logo"  type="zolagoudmspro/vendor_logo" template="unirgy/microsite/vendor.logo.phtml"}}
</div>
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

