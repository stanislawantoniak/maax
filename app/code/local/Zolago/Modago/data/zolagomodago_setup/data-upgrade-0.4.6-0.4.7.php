<?php
$cms = array(
	array(
		'title'         => 'Account empty order process',
		'identifier'    => 'account-order-process-empty',
		'content'       => '<p>Nie masz zamówień w realizacji. Zapraszamy do zakupów.</p>',
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