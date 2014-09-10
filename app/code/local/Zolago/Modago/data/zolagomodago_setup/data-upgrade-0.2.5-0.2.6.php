<?php

$cms = array(
    array(
        'title'         => 'Checkout thank you page',
        'identifier'    => 'checkout-thank-you-page',
        'content'       =>
            <<<EOD
<aside class="col-md-10 col-md-push-1 col-sm-12 section helpful-vote">
    <div class="main bg-w">
        <header>
            <h2>Dziękujemy za złożenie zamówienia!</h2>
        </header>
        <p>Twoje zamówienie zostało przyjęte i zostanie zrealizowane po potwierdzeniu otrzymania płatności. test test 123</p>
    </div>
</aside>
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

