<?php

$cms = array(
    array(
        'title'         => 'Help page',
        'identifier'    => 'help-page',
        'content'       =>
<<<EOD
<div class="container-fluid">
 <div id="help-index">
  <div class="row">
   <div class="bg-w col-sm-6 col-xs-12">I</div>
   <div class="bg-w col-sm-6 col-xs-12">II</div>
  </div>
  <div class="row">
   <div class="bg-w col-sm-6 col-xs-12">III</div>
   <div class="bg-w col-sm-6 col-xs-12">IV</div>
  </div>
  <div class="row">
   <div class="bg-w col-sm-6 col-xs-12">V</div>
   <div class="bg-w col-sm-6 col-xs-12">VI</div>
  </div>
 </div>
</div>
EOD
    ,
        'is_active'     => 1,
        'stores'        => 0,
    )
);

foreach ($cms as $data) {
    $block = Mage::getModel('cms/block')->load($data['identifier']);
    if ($block->getBlockId()) {
        $oldData = $block->getData();
        $data = array_merge($oldData,$data);
    }

    $block->setData($data)->save();
}