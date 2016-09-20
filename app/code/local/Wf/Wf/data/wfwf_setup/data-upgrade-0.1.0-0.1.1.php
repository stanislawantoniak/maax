<?php
// installation of top_bar cms block
$cmsNavigationBlocks = array(
    array(
        'title'         => 'WF: Top Bar',
        'identifier'    => 'wf_top_bar',
        'content'       => <<<EOD
                    <div class="top-bar">
                <div class="container-fluid">
                    <div class="top-bar-left">
                        <div class="top-bar-left-content">
                            <ul class="links">
                                <li><a href="{{store direct_url='' _no_vendor='1'}}">DOSTAWA GRATIS 24h</a></li>
                                <li><a href="{{store direct_url='' _no_vendor='1'}}">DARMOWY ZWROT 30 dni</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="top-bar-right hidden-xs">
                        <div id="nav_menu-4" class="topbar-widget widget_nav_menu">
                            <div class="menu-right-top-menu-container">
                                <ul id="menu-right-top-menu" class="menu">
                                    <li><a href="{{store direct_url='storesmap' _no_vendor='1'}}">Znajdź sklep</a></li>
                                    <li><a href="{{store direct_url='help/contact' _no_vendor='1'}}">Kontakt</a></li>
                                    <li><a href="{{store direct_url='wishlist' _no_vendor='1'}}">Ulubione</a></li>
                                    <li><a href="{{store direct_url='customer/account' _no_vendor='1'}}">Twoje Konto</a></li>
                                </ul>
                            </div>
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

foreach ($cmsNavigationBlocks as $data) {
    Mage::getModel('cms/block')->setData($data)->save();
}

