<?php
// installation of top_bar cms block
$cmsNavigationBlocks = array(
    array(
        'title'         => 'WF: Top Bar',
        'identifier'    => 'wf_top_bar',
        'content'       => <<<EOD
                    <div class="top-bar">
                <div class="container">
                    <div class="top-bar-left">
                        <div class="top-bar-left-content">
                            <ul class="links">
                                <li><a href="#">DOSTAWA GRATIS 24h</a></li>
                                <li><a href="#">DARMOWY ZWROT 30 dni</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="top-bar-right">
                        <div id="nav_menu-4" class="topbar-widget widget_nav_menu">
                            <div class="menu-right-top-menu-container">
                                <ul id="menu-right-top-menu" class="menu">
                                    <li><a href="/">Znajdź sklep</a></li>
                                    <li><a href="/">Kontakt</a></li>
                                    <li><a href="/">Ulubione</a></li>
                                    <li><a href="/">Twoje Konto</a></li>
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

