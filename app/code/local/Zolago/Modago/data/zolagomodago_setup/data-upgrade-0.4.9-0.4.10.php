<?php

// installation of bottom header cms blocks
$cmsNavigationBlocks = array(
    array(
        'title'         => 'Top bottom header desktop vendor Esotiq new',
        'identifier'    => 'top-bottom-header-desktop-v-4',
        'content'       => <<<EOD
<div class="container-fluid vendor-top-bottom-header action-box-bundle clearfix">
<div class="col-md-6 col-sm-4 logo-part">
{{block name="vendor.logo"  vendor_label="Hello" type="zolagoudmspro/vendor_logo" template="unirgy/microsite/vendor.logo.phtml"}}
</div>
<div class="col-md-6 col-sm-8 hidden-xs text-right">
  <ul class="vendor-top-bottom-header-links">
    <li><a  data-target="#seller_description"  data-toggle="modal">o sklepie</a></li>
    <li><a data-target="#terms_delivery" data-toggle="modal" >dostawa i zwrot</a></li>
    <li><a data-target="#ask_question" data-toggle="modal" >zadaj pytanie</a></li>
  </ul>
</div>

</div>
{{block name="vendor.info" type="zolagoudmspro/vendor_info" template="unirgy/microsite/vendor.info.phtml"}}
EOD
    ,
        'is_active'     => 1,
        'stores'        => 0
    ),
    array(
        'title'         => 'Top bottom header mobile vendor Esotiq new',
        'identifier'    => 'top-bottom-header-mobile-v-4',
        'content'       => <<<EOD
<div class="container-fluid vendor-top-bottom-header action-box-bundle clearfix">
<div class="col-md-6 col-sm-4 logo-part">
{{block name="vendor.logo"  vendor_label="Hello" type="zolagoudmspro/vendor_logo" template="unirgy/microsite/vendor.logo.phtml"}}
</div>

</div>
{{block name="vendor.info" type="zolagoudmspro/vendor_info" template="unirgy/microsite/vendor.info.phtml"}}
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

