<?php

$cms = array(
    array(
        'title'         => 'Checkout thank you page',
        'identifier'    => 'checkout-thank-you-page',
        'content'       =>
            <<<EOD
<header>
    <h2>Dziękujemy za złożenie zamówienia!</h2>
</header>
<p>Twoje zamówienie zostało przyjęte. Jeśli zamówienie opłacałaś/eś online zostanie ono zrealizowane po potwierdzeniu otrzymania płatności.<br />
    Stan zamówienia możesz monitorować logując się do swojego konta w serwisie. Szczegóły zamówienia zostały właśnie wysłane mailowo.</p>
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

