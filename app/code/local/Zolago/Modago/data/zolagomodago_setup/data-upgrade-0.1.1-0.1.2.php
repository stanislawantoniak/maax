<?php
/**
 * Author: Paweł Chyl <pawel.chyl@orba.pl>
 * Date: 08.07.2014
 */

// installation of footer cms blocks
$cmsNavigationBlocks = array(
    array(
        'title'         => 'Menu hamburgerowe',
        'identifier'    => 'navigation-sliding-left',
        'content'       => <<<EOD
<aside class="sb-slidebar sb-left">
    <header>
        <a class="closeSlidebar" href="#"><i class="fa fa-chevron-left"></i> zwiń menu</a>
    </header>
    <div class="sb-slidebar-inner">
        <nav><!-- ALTERNATYWNA PRZERWA MIĘDZY POZYCJAMI LISTY <li class="separator"></li> -->
            <ul class="sb-menu">
                {{block type='zolagomodago/page_aside' name='aside.header.sliding' template='page/html/aside/header.menu.sliding.phtml'}}
                <li><a href="#">WYBIERZ SKLEP</a></li>
                <li><a href="#">OUTLET</a></li>
            </ul>
            <ul class="sb-menu">
                <li><a href="#">TWOJE KONTO</a></li>
                <li><a href="#">TWOJE ULUBIONE</a></li>
                <li><a href="#">W TWOIM STYLU</a> </li>
            </ul>
            <ul class="sb-menu">

                <li><a href="#">POMOC</a></li>
                <li><a href="#">O FIRMIE</a></li>
                <li><a href="#">Kontakt</a></li>
            </ul>
        </nav>
    </div>
</aside>
EOD
,
        'is_active'     => 1,
        'stores'        => 0
    )
);

foreach ($cmsNavigationBlocks as $data) {
    Mage::getModel('cms/block')->setData($data)->save();
}

