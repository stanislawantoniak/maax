<?php
$cms = array(
    array(
        'title'         => 'wishlist not logged empty',
        'identifier'    => 'wishlist-not-logged-empty',
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
                <p style="font-family: latoblack">Ojej, nie masz jeszcze ulubionych?</p>
                <p style="font-family: latoblack">Stwórz listę ulubionych produktów! <span style="font-family: latoregular;">Wystarczy kliknąć w <img class="img-02" src="/skin/frontend/modago/default/images/heart-like.png"> przy produkcie.</span></p>
                <br>
                <p>Podoba Ci się jakiś ciuch, ale nie chcesz go od razu kupować? Dodaj do ulubionych by wygodnie do niego wrócić.</p>
                <p>Na Twoje ulubione produkty postaramy sie przygotować promocje i rabaty.</p>
                <br>
                <br>
                <p style="font-size: 12px;">Chcesz mieć zawsze dostęp do swoich ulubionych? Zaloguj się lub załóż konto żeby zostały zapamiętane zawsze jak się zalogujesz, niezależne od miejsca i urządzenia. <a href="/customer/account/login/" class="underline">Zaloguj&nbsp;się&nbsp;do&nbsp;konta</a></p>
                <p style="font-size: 12px;">Nie masz jeszcze konta? Utwórz konto w zaledwie 10 sekund! <a href="/customer/account/create/" class="underline">Załóż&nbsp;konto</a></p>
            </div>
        </div>
    </div>
</div>
EOD
,
        'is_active'     => 1,
        'stores'        => 0
    ),
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
                <p>Zaloguj się lub załóż konto żeby zostały zapamiętane zawsze jak się zalogujesz, niezależne od miejsca i urządzenia. <a href="/customer/account/login/" class="button button-third" style="margin-left: 5px; margin-right: 5px;">ZALOGUJ&nbsp;SIĘ</a></p>
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
    ),
    array(
        'title'         => 'wishlist logged empty',
        'identifier'    => 'wishlist-logged-empty',
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
                <p style="font-family: latoblack">Ojej, nie masz jeszcze ulubionych?</p>
                <p style="font-family: latoblack">Stwórz listę ulubionych produktów! <span style="font-family: latoregular;">Wystarczy kliknąć w <img class="img-02" src="/skin/frontend/modago/default/images/heart-like.png"> przy produkcie.</span></p>
                <br>
                <p>Podoba Ci się jakiś ciuch, ale nie chcesz go od razu kupować? Dodaj do ulubionych by wygodnie do niego wrócić.</p>
                <p>Na Twoje ulubione produkty postaramy sie przygotować promocje i rabaty.</p>
            </div>
        </div>
    </div>
</div>
EOD
    ,
        'is_active'     => 1,
        'stores'        => 0
    ),
    array(
        'title'         => 'wishlist logged full',
        'identifier'    => 'wishlist-logged-full',
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