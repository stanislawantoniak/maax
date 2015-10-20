<?php

// installation Vendor regulations accept EXPIRED text cms blocks
$cms =
	array(
		array(
			'title' => 'Vendor regulations accept EXPIRED text',
			'identifier' => 'vendor_regulations_accept_expired',
			'content' =>
				<<<EOD
                <p><b> :(      Your confirmation code has expired. PLEASE contact GALLERY </b> </p>
<p>
Lorem Ipsum is simply dummy text of the printing and typesetting industry.
Lorem Ipsum has been the industry's standard dummy text ever since the 1500s,
when an unknown printer took a galley of type and scrambled it to make a type specimen book.</p>
<p>
Lorem Ipsum is simply dummy text of the printing and typesetting industry.
Lorem Ipsum has been the industry's standard dummy text ever since the 1500s,
when an unknown printer took a galley of type and scrambled it to make a type specimen book.</p>
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