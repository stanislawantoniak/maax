<?php
$cms = array(
	array(
		'title'         => 'Account attach orders',
		'identifier'    => 'account-order-process-attach',
		'content'       => '<p>W naszej bazie widzimy zamówienia składane na Twój adres e-mail, sprzed założenia konta. Czy chcesz przypiąć te
							zamówienia do swojego konta, aby mieć wgląd w historię zamówień i możliwość szybkiego zgłoszenia zwrotu lub
							reklamacji?</p>',
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