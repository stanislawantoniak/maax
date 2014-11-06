<?php

$cms = array(
    array(
        'title'         => 'Account empty order history',
        'identifier'    => 'account-order-history-empty',
        'content'       =>
            <<<EOD
<section>
	<div id="account-order-history-empty" class="bg-w main">
		    {{block type="zolagomodago/sales_order_history_text" name="sales.order.history.text"}}		       
			<p>Sprawdź nasze <a href="#promocje" class="underline">promocje</a> już teraz.</p>
			<a id="back" class="button button-third large pull-left">Wróć</a>
	</div>
</section>
EOD
    ,
        'is_active'     => 1,
        'stores'        => 0
    ),
    array(
        'title'         => 'Account empty order history text',
        'identifier'    => 'account-order-history-empty-text',
        'content'       =>
            <<<EOD
			<p>Nie masz jeszcze zamówień? Niemożliwe!</p>
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