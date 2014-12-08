<?php

$cms = array(
	array(
		'title'         => 'Rejestracja prawy blok',
		'identifier'    => 'customer-register-right',
		'content'       =>
			<<<EOD
<p>
	Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur eget lectus diam. In aliquam in
	felis eu pulvinar. Vivamus finibus metus eget ipsum malesuada euismod. Fusce condimentum rhoncus
	nulla eu lacinia. Aliquam auctor faucibus magna, a tristique arcu tempor consectetur. Phasellus
	commodo purus ex, non rhoncus ligula tincidunt vel.</p>
<p>
	Maecenas a ullamcorper urna, et imperdiet ex. Pellentesque ultricies enim quis lectus fringilla,
	sed convallis enim lobortis. Vivamus posuere a mauris eu vehicula. Curabitur placerat nulla sed
	elit eleifend iaculis.
</p>
EOD
	,
		'is_active'     => 1,
		'stores'        => 0
	),

);

foreach ($cms as $data) {
	$block = Mage::getModel('cms/block')->load($data['identifier']);
	if ($block->getBlockId()) {
		$oldData = $block->getData();
		$data = array_merge($oldData,$data);
	}

	$block->setData($data)->save();
}