<?php
//remove old blocks (just to be sure that everything is set up right
$blocksToRemove = Mage::getModel('cms/block')->getCollection();
$blocksToRemove->addFieldToFilter("identifier", array('in' => array("wishlist-not-logged-empty","wishlist-not-logged-full","wishlist-logged-empty","wishlist-logged-full")));
foreach($blocksToRemove as $blockToRemove) {
	$blockToRemove->delete();
}

//recreate blocks with correct scopes
$allStores = 0;
$modagoStore =  Mage::app()->getStore('default')->getId();

$blocksToCreate = array(
	array(
		'title' => 'wishlist not logged empty (Modago.pl)',
		'identifier' => 'wishlist-not-logged-empty',
		'content' =>
			<<<EOD
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
		'is_active' => 1,
		'stores' => $modagoStore
	),
	array(
		'title' => 'wishlist not logged empty (default)',
		'identifier' => 'wishlist-not-logged-empty',
		'content' =>
			<<<EOD
<div class="wishlist-cms wrapp-section bg-w">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12 wishlist-cms-title">
                <h1>
                    <span class="span-title uppercase fontBlack">Twoje ulubione</span>
                </h1>
            </div>
        </div>
        <div class="row">
            <div class="wishlist-cms-content col-sm-12">
                <p class="fontBlack">Ojej, nie masz jeszcze ulubionych?</p>
                <p class="fontBlack">Stwórz listę ulubionych produktów! <span class="fontRegular">Wystarczy kliknąć w <img class="img-02" src="/skin/frontend/modago/default/images/heart-like.png"> przy produkcie.</span></p>
                <br/>
                <p>Podoba Ci się jakiś ciuch, ale nie chcesz go od razu kupować? Dodaj do ulubionych by wygodnie do niego wrócić.</p>
                <br/>
                <br/>
                <p style="font-size: 12px;">Chcesz mieć zawsze dostęp do swoich ulubionych? Zaloguj się lub załóż konto żeby zostały zapamiętane zawsze jak się zalogujesz, niezależne od miejsca i urządzenia. <a href="/customer/account/login/" class="underline">Zaloguj&nbsp;się&nbsp;do&nbsp;konta</a></p>
                <p style="font-size: 12px;">Nie masz jeszcze konta? Utwórz konto w zaledwie 10 sekund! <a href="/customer/account/create/" class="underline">Załóż&nbsp;konto</a></p>
            </div>
        </div>
    </div>
</div>
EOD
	,
		'is_active' => 1,
		'stores' => $allStores
	),
	array(
		'title' => 'wishlist not logged full (Modago.pl)',
		'identifier' => 'wishlist-not-logged-full',
		'content' =>
			<<<EOD
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
		'is_active' => 1,
		'stores' => $modagoStore
	),
	array(
		'title' => 'wishlist not logged full (default)',
		'identifier' => 'wishlist-not-logged-full',
		'content' =>
			<<<EOD
<div class="wishlist-cms wrapp-section bg-w">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12 wishlist-cms-title">
                <h1>
                    <span class="span-title uppercase fontBlack">Twoje ulubione</span>
                </h1>
            </div>
        </div>
        <div class="row">
            <div class="wishlist-cms-content col-sm-12">
                <p class="fontBlack">Chcesz mieć zawsze dostęp do swoich ulubionych?</p>
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
		'is_active' => 1,
		'stores' => $allStores
	),
	array(
		'title' => 'wishlist logged empty (Modago.pl)',
		'identifier' => 'wishlist-logged-empty',
		'content' =>
			<<<EOD
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
		'is_active' => 1,
		'stores' => $modagoStore
	),
	array(
		'title' => 'wishlist logged empty (default)',
		'identifier' => 'wishlist-logged-empty',
		'content' =>
			<<<EOD
<div class="wishlist-cms wrapp-section bg-w">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12 wishlist-cms-title">
                <h1>
                    <span class="span-title uppercase fontBlack">Twoje ulubione</span>
                </h1>
            </div>
        </div>
        <div class="row">
            <div class="wishlist-cms-content col-sm-12">
                <p class="fontBlack">Ojej, nie masz jeszcze ulubionych?</p>
                <p class="fontBlack">Stwórz listę ulubionych produktów! <span class="fontRegular">Wystarczy kliknąć w <img class="img-02" src="/skin/frontend/modago/default/images/heart-like.png"> przy produkcie.</span></p>
                <br>
                <p>Podoba Ci się jakiś ciuch, ale nie chcesz go od razu kupować? Dodaj do ulubionych by wygodnie do niego wrócić.</p>
            </div>
        </div>
    </div>
</div>
EOD
	,
		'is_active' => 1,
		'stores' => $allStores
	),
	array(
		'title' => 'wishlist logged full (Modago.pl)',
		'identifier' => 'wishlist-logged-full',
		'content' =>
			<<<EOD
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
		'is_active' => 1,
		'stores' => $modagoStore
	),
	array(
		'title' => 'wishlist logged full (default)',
		'identifier' => 'wishlist-logged-full',
		'content' =>
			<<<EOD
<div class="wishlist-cms wrapp-section bg-w">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12 wishlist-cms-title">
                <h1>
                    <span class="span-title uppercase fontBlack">Twoje ulubione</span>
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
		'is_active' => 1,
		'stores' => $allStores
	)
);

foreach ($blocksToCreate as $blockData) {
    $collection = Mage::getModel('cms/block')->getCollection();
    $collection->addStoreFilter($blockData['stores']);
    $collection->addFieldToFilter('identifier',$blockData["identifier"]);
    $currentBlock = $collection->getFirstItem();

    if ($currentBlock->getBlockId()) {
        $oldBlock = $currentBlock->getData();
	    $blockData = array_merge($oldBlock, $blockData);
    }
	$currentBlock->setData($blockData)->save();
}