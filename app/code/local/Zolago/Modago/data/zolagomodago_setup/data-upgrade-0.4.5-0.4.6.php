<?php
$cms = array(
	array(
		'title'         => 'Account empty order history',
		'identifier'    => 'account-order-history-empty',
		'content'       =>
			<<<EOD
		    {{block type="zolagomodago/sales_order_history_text" name="sales.order.history.text"}}
			<p>Sprawdź nasze <a href="#promocje" class="underline">promocje</a> już teraz.</p>
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