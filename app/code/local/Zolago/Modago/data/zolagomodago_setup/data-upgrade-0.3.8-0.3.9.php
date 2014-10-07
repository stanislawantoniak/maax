<?php

$cms = array(
	array(
		'title'         => 'Help page mobile menu',
		'identifier'    => 'help-page-mobile-menu',
		'content'       =>
<<<EOD
<div id="help-mobile-menu">
    <div class="wrapp-section bg-w visible-xs">
        <h1><a href="{{store url='help'}}">Pomoc</a></h1>
    </div>
</div>
EOD
	,
		'is_active'     => 1,
		'stores'        => 0
	));

foreach ($cms as $data) {
	$block = Mage::getModel('cms/block')->load($data['identifier']);
	if ($block->getBlockId()) {
		$oldData = $block->getData();
		$data = array_merge($oldData,$data);
	}

	$block->setData($data)->save();
}

