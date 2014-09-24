<?php

$cms = array(
    array(
        'title'         => 'Account empty order history',
        'identifier'    => 'account-order-history-empty',
        'content'       =>
            <<<EOD
<div id="account-order-history-empty">
    <div class="row">
        <p>Nie masz jeszcze zamówień? Niemożliwe!</p>
        <p>Sprawdź nasze <a href="#promocje">promocje</a> już teraz.
    </div>
    <div class="row action">
       <div class="col-sm-12">
           <a id="back" class="button button-third large pull-left">Wróć</a>
       </div>
    </div>
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