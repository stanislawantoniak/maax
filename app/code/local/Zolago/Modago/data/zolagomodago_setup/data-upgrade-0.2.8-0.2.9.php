<?php

$cms = array(
    array(
        'title'         => 'Account empty order history',
        'identifier'    => 'account-order-history-empty',
        'content'       =>
            <<<EOD
<div id="account-order-history-empty" class="bg-w main">
	<p>Nie masz jeszcze zamówień? Niemożliwe!</p>
	<p>Sprawdź nasze <a href="{{store direct_url='#promotions'}}" class="underline">promocje</a> już teraz.</p>
	<a id="back" class="button button-third large pull-left">Wróć</a>
</div>
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