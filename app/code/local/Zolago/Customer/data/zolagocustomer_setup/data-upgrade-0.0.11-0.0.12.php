<?php

$data = array(
	'title'         => 'Wiadomość po wylogowaniu',
	'identifier'    => 'customer-logout-forget',
	'content'       => 'Zostałeś wylogowany poprawnie. {{block type=\'zolagopersistent/forget_logoutlink\' anchor_text=\'Usuń moje dane z urządzenia.\'}}',
	'is_active'     => 1,
	'stores'        => 0
);

$block = Mage::getModel('cms/block')->load($data['identifier']);
if ($block->getBlockId()) {
	$oldData = $block->getData();
	$data = array_merge($oldData,$data);
}
$block->setData($data)->save();
