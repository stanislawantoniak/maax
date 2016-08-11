<?php

$cmsNavigationBlocks = array(

	array(
		'title'         => 'Cookie restriction notice',
		'identifier'    => 'cookie_restriction_notice_block',
		'content'       => <<<EOD
Strona korzysta z plików cookie w celu realizacji usług zgodnie z <a href="{{store direct_url="privacy-policy-cookie-restriction-mode"}}">Polityką prywatności</a>. Możesz określić warunki przechowywania lub dostępu do cookie w Twojej przeglądarce.
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

