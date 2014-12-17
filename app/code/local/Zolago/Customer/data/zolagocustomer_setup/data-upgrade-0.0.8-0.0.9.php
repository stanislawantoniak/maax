<?php

$data = array(
	'title'         => 'Wiadomość po wylogowaniu',
	'identifier'    => 'customer-logout-forget',
	'content'       => 'Zostałeś wylogowany poprawnie. Kliknij <a href="{{store url=\'persistent/index/forget\' _no_vendor=\'1\'}}">tutaj</a> żeby wyczyścić koszyk i ulubione.',
	'is_active'     => 1,
	'stores'        => 0
);

$block = Mage::getModel('cms/block')->load($data['identifier']);
if ($block->getBlockId()) {
	$oldData = $block->getData();
	$data = array_merge($oldData,$data);
}
$block->setData($data)->save();
