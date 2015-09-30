<?php
$cmsNavigationBlocks = array(
    array(
        'title'         => 'Checkout | Right column | Step 1 | Guest',
        'identifier'    => 'checkout-right-column-step-1-guest',
        'content'       => <<<EOD
<div class="sidebar-second col-lg-3 col-md-4 col-sm-4 col-xs-12 col-lg-push-9 col-md-push-8 col-sm-push-8 hidden-xs">
    <div class="main bg-w">
        <div class="checkout-sidebar-second-list-title">
            Kupując w&nbsp;Modago otrzymujesz:
        </div>
        <ul class="checkout-sidebar-second-ul">
            <li class="checkout-sidebar-second-li checkout-sidebar-second-li-return">
                <div class="checkout-sidebar-second-li-strong">
                    30 dniowy
                </div>
                <div class="checkout-sidebar-second-li-normal">
                    darmowy zwrot
                </div>
            </li>
            <li class="checkout-sidebar-second-li checkout-sidebar-second-li-shipping">
                <div class="checkout-sidebar-second-li-strong">
                    Błyskawiczną
                </div>
                <div class="checkout-sidebar-second-li-normal">
                    wysyłkę
                </div>
            </li>
            <li class="checkout-sidebar-second-li checkout-sidebar-second-li-payments">
                <div class="checkout-sidebar-second-li-strong">
                    Wygodne
                </div>
                <div class="checkout-sidebar-second-li-normal">
                    formy płatności
                </div>
            </li>
            <li class="checkout-sidebar-second-li checkout-sidebar-second-li-safe">
                <div class="checkout-sidebar-second-li-strong">
                    Bezpieczne
                </div>
                <div class="checkout-sidebar-second-li-normal">
                    zakupy
                </div>
            </li>
        </ul>
    <div class="checkout-sidebar-second-list-title">
            Załóż konto aby&nbsp;mieć:
        </div>
        <ul class="checkout-sidebar-second-ul">
            <li class="checkout-sidebar-second-li checkout-sidebar-second-li-status">
                <div class="checkout-sidebar-second-li-strong">
                    Stały wzgląd w staus
                </div>
                <div class="checkout-sidebar-second-li-normal">
                    zamówienia czy zwrotu
                </div>
            </li>
            <li class="checkout-sidebar-second-li checkout-sidebar-second-li-simple">
                <div class="checkout-sidebar-second-li-strong">
                    Uproszczony proces
                </div>
                <div class="checkout-sidebar-second-li-normal">
                    składania zamówień
                </div>
            </li>
            <li class="checkout-sidebar-second-li checkout-sidebar-second-li-promos">
                <div class="checkout-sidebar-second-li-strong">
                    Dodatkowe
                </div>
                <div class="checkout-sidebar-second-li-normal">
                    rabaty i promocje
                </div>
            </li>
        </ul>
    </div>
</div>
EOD
    ,
        'is_active'     => 1,
        'stores'        => 0
    )
);

foreach ($cmsNavigationBlocks as $data) {
    $block = Mage::getModel('cms/block')->load($data['identifier']);
    if ($block->getBlockId()) {
        $oldData = $block->getData();
        $data = array_merge($oldData,$data);
    }
    $block->setData($data)->save();
}