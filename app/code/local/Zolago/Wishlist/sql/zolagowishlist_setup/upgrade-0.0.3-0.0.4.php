<?php
$cms = array(

    array(
        'title'         => 'wishlist not logged full',
        'identifier'    => 'wishlist-not-logged-full',
        'content'       => <<<EOD
<div class="wishlist-cms wrapp-section bg-w">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12 wishlist-cms-title">
                <h1>
                    <span style="font-family: latoblack" class="span-title uppercase">Twoje ulubione</span>
                </h1>
            </div>
        </div>
        <div class="row">
            <div class="wishlist-cms-content col-sm-12">
                <p style="font-family: latoblack">Chcesz mieć zawsze dostęp do swoich ulubionych?</p>
                <p style="margin-right: 5px; display: inline;">Zaloguj się lub załóż konto żeby zostały zapamiętane zawsze jak się zalogujesz, niezależne od miejsca i urządzenia. </p><a href="/customer/account/login/" class="button button-third">ZALOGUJ&nbsp;SIĘ</a>
                <p>Nie masz jeszcze konta? Utwórz konto w zaledwie 10 sekund! <a href="/customer/account/create/" class="underline">Załóż&nbsp;konto</a></p>
                <br>
                <p style="font-size: 12px;">Możesz usunąć produkt z listy ulubionych klikając na serduszko.</p>
            </div>
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