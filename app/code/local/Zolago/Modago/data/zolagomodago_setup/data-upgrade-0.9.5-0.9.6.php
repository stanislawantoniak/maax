<?php
// Delete modago prefix
$newData = array (
	array (
		'identifier' => "modago-login-continue-normal"
	),
	array (
		'identifier' => "modago-login-continue-checkout"
	)
);

foreach ($newData as $data) {
	$block = Mage::getModel('cms/block')->load($data['identifier']);
	if ($block->getBlockId()) {
		$data['identifier'] = substr($data['identifier'], strpos($data['identifier'], "modago-") + 7);
		$oldData = $block->getData();
		$data = array_merge($oldData,$data);
	}

	$block->setData($data)->save();
}
