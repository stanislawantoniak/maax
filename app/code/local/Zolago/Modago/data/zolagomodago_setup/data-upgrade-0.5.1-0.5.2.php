<?php

// installation of bottom header cms blocks
$cmsNavigationBlocks = array(

	array(
		'title'         => 'Twoje promocje nagłówek formularza logowania',
		'identifier'    => 'promotions-login-header',
		'content'       => <<<EOD
<h2>Chcesz skorzystać z super promocji?</h2>
<p class="fz_11">Załóż konto i zapisz się do newslettera żeby kupować taniej!</p>
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

