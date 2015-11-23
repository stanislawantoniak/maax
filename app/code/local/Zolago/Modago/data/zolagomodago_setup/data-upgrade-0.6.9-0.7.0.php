<?php
//remove old blocks (just to be sure that everything is set up right
$blocksToRemove = Mage::getModel('cms/block')->getCollection();
$blocksToRemove->addFieldToFilter("identifier", array('in' => array("navigation-sliding-left")));
foreach($blocksToRemove as $blockToRemove) {
	$blockToRemove->delete();
}

//recreate blocks with correct scopes
$allStores = 0;
$modagoStore =  Mage::app()->getStore('default')->getId();

$blocksToCreate = array(
	array(
		'title' => 'Menu hamburgerowe (Modago.pl)',
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
                    <a href="{{store direct_url='modago/brands' _no_vendor='1'}}">
                        <img src="{{skin url='images/svg/label.svg'}}" class="hamburger-ico ico-label">MARKI
                    </a>
                </li>
            </ul>
            <ul class="sb-menu">
                <li>
                    <a href="{{store direct_url='customer/account' _no_vendor='1'}}">
                        <img src="{{skin url='images/svg/user.svg'}}" class="hamburger-ico ico-user">TWOJE KONTO
                    </a>
                </li>
                <li>
                    <a href="{{store direct_url='wishlist' _no_vendor='1'}}">
                        <img src="{{skin url='images/svg/favourite.svg'}}" class="hamburger-ico ico-favourite">TWOJE ULUBIONE
                    </a>
                </li>
            </ul>
            <ul class="sb-menu">
                <li>
                    <a href="{{store direct_url='help' _no_vendor='1'}}">
                        <img src="{{skin url='images/svg/help.svg'}}" class="hamburger-ico ico-label">POMOC
                    </a>
                </li>
                <li>
                    <a href="{{store direct_url='help/contact/gallery' _no_vendor='1'}}">
                        <img src="{{skin url='images/svg/contact.svg'}}" class="hamburger-ico ico-label">KONTAKT
                    </a>
                </li>
                <li>
                    <a href="{{store direct_url='regulamin-modago' _no_vendor='1'}}">
                        <img src="{{skin url='images/svg/tos.svg'}}" class="hamburger-ico ico-label">REGULAMIN
                    </a>
                </li>
                <li>
                    <a href="{{store direct_url='informacje-o-modago' _no_vendor='1'}}">
                        <img src="{{skin url='images/svg/aboutus.svg'}}" class="hamburger-ico ico-label">O NAS
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>
EOD
	,
		'is_active' => 1,
		'stores' => $modagoStore
	),
	array(
		'title' => 'Menu hamburgerowe (Modago.pl)',
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
            </ul>
            <ul class="sb-menu">
                <li>
                    <a href="{{store direct_url='customer/account' _no_vendor='1'}}">
                        <img src="{{skin url='images/svg/user.svg'}}" class="hamburger-ico ico-user">TWOJE KONTO
                    </a>
                </li>
                <li>
                    <a href="{{store direct_url='wishlist' _no_vendor='1'}}">
                        <img src="{{skin url='images/svg/favourite.svg'}}" class="hamburger-ico ico-favourite">TWOJE ULUBIONE
                    </a>
                </li>
            </ul>
            <ul class="sb-menu">
                <li>
                    <a href="{{store direct_url='help' _no_vendor='1'}}">
                        <img src="{{skin url='images/svg/help.svg'}}" class="hamburger-ico ico-label">POMOC
                    </a>
                </li>
                <li>
                    <a href="{{store direct_url='help/contact/gallery' _no_vendor='1'}}">
                        <img src="{{skin url='images/svg/contact.svg'}}" class="hamburger-ico ico-label">KONTAKT
                    </a>
                </li>
                <li>
                    <a href="{{store direct_url='regulamin-modago' _no_vendor='1'}}">
                        <img src="{{skin url='images/svg/tos.svg'}}" class="hamburger-ico ico-label">REGULAMIN
                    </a>
                </li>
                <li>
                    <a href="{{store direct_url='informacje-o-modago' _no_vendor='1'}}">
                        <img src="{{skin url='images/svg/aboutus.svg'}}" class="hamburger-ico ico-label">O NAS
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>
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