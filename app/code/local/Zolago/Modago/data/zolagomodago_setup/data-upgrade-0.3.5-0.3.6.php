<?php

$cms = array(
	array(
		'title'         => 'Contact with Modago',
		'identifier'    => 'help-contact-gallery',
		'content'       =>
			<<<EOD
<p>
	<h1>Kontakt z galerią</h1>
</p>
EOD
	,
		'is_active'     => 1,
		'stores'        => 0
	),
	array(
		'title'         => 'Contact with Vendor',
		'identifier'    => 'help-contact-vendor',
		'content'       =>
			<<<EOD
<p>
	<h1>Kontakt ze sklepem</h1>
</p>
EOD
	,
		'is_active'     => 1,
		'stores'        => 0
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