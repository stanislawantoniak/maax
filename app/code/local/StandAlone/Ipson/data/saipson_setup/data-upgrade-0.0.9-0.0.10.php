<?php

$blocks = array(
    array(
        'title' => 'Menu hamburgerowe',
        'identifier' => 'navigation-sliding-left',
        'content' =>
<<<EOD
<aside class="sb-slidebar sb-left">
    <header>
        <a class="closeSlidebar">zwiń menu</a>
    </header>
    <div class="sb-slidebar-inner">
        <nav><!-- ALTERNATYWNA PRZERWA MIĘDZY POZYCJAMI LISTY <li class="separator"></li> -->
            <ul class="sb-menu">
                {{block type='zolagomodago/page_aside' name='aside.header.sliding' template='page/html/aside/header.menu.sliding.phtml'}}
                <li>
                    <a href="{{store direct_url='wypozyczalnia'}}">
                        <i class="fa fa-exchange" aria-hidden="true" style="font-size: 20px;margin: 0 12px;"></i>WYPOŻYCZALNIA
                    </a>
                </li>
                <li>
                    <a href="{{store direct_url='blog'}}">
                        <i class="fa fa-info-circle" aria-hidden="true" style="font-size: 20px;margin: 0 13px;"></i>PORADNIKI
                    </a>
                </li>
            </ul>
            <ul class="sb-menu">
                <li>
                    <a href="{{store direct_url='customer/account' }}">
                        <img src="{{skin url='images/svg/user.svg' _no_protocol='1'}}" class="hamburger-ico ico-user">TWOJE KONTO
                    </a>
                </li>
                <li>
                    <a href="{{store direct_url='wishlist'}}">
                        <i class="fa fa-list-ul" aria-hidden="true" style="font-size: 20px;margin: 0 12px;"></i>LISTA ZAKUPÓW
                    </a>
                </li>
            </ul>
            <ul class="sb-menu">
                <li>
                    <a href="{{store direct_url='dostawa'}}">
                        <i class="fa fa-truck" aria-hidden="true" style="font-size: 20px;margin: 0 12px;"></i>DOSTAWA
                    </a>
                </li>
                <li>
                    <a href="{{store direct_url='zwroty-i-reklamacje'}}">
                        <i class="fa fa-refresh" aria-hidden="true" style="font-size: 20px;margin: 0 13px;"></i>ZWROTY I REKLAMACJE
                    </a>
                </li>
                <li>
                    <a href="{{store direct_url='help/contact/gallery'}}">
                        <img src="{{skin url='images/svg/contact.svg' _no_protocol='1'}}" class="hamburger-ico ico-label">KONTAKT
                    </a>
                </li>
                <li>
                    <a href="{{store direct_url='regulamin' }}">
                        <img src="{{skin url='images/svg/tos.svg' _no_protocol='1'}}" class="hamburger-ico ico-label">REGULAMIN
                    </a>
                </li>
                <li>
                    <a href="{{store direct_url='privacy-policy-cookie-restriction-mode'}}">
                        <img src="{{skin url='images/svg/privacy.svg' _no_protocol='1'}}" class="hamburger-ico ico-label">Polityka prywatności
                    </a>
                </li>
                <li>
                    <a href="{{store direct_url='o-nas'}}">
                        <img src="{{skin url='images/svg/aboutus.svg' _no_protocol='1'}}" class="hamburger-ico ico-label">O NAS
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>
EOD
,
        'is_active'     => 1,
        'stores'        => 0
    ),
);

foreach ($blocks as $blockData) {
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

