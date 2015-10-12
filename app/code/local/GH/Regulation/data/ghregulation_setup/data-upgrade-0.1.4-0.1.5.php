<?php

// installation Vendor regulations accept text cms blocks
$cms =
	array(
		array(
			'title' => 'Vendor regulations  ACCEPTED text',
			'identifier' => 'vendor_regulations_accept_accepted',
			'content' =>
				<<<EOD
<p>regulamin zaakceptowany przez login vendora
                                        </p>
EOD
		,
			'is_active' => 1,
			'stores' => 0

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