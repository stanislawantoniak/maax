<?php
// installation of top_bar cms block
$cmsNavigationBlocks = array(
    array(
        'title'         => 'Top Bar',
        'identifier'    => 'standalone_top_bar',
        'content'       => <<<EOD
            <div class="top-bar">
                <div class="container-fluid">
                    <div class="top-bar-left">
                        <div class="top-bar-left-content">
                            <ul class="links">
                                <li><a class="info-line-link" href="{{store direct_url=''}}"><i class="fa fa-mobile"></i>Bezpłatna infolinia: 800 182 022</a></li>
                                <li><a class="delivery-link" href="{{store direct_url=''}}"><i class="fa fa-truck"></i>Wysyłka 24h</a></li>
                                <li><a class="return-link" href="{{store direct_url=''}}"><i class="fa fa-refresh"></i>14 dni na zwrot</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="top-bar-right hidden-xs">
                        <div id="nav_menu-4" class="topbar-widget widget_nav_menu">
                            <div class="menu-right-top-menu-container">
                                <ul id="menu-right-top-menu" class="menu">
                                    <li><a href="{{store direct_url='help/contact'}}">Kontakt</a></li>
                                    <li><a href="{{store direct_url='wishlist'}}">Lista zakupów</a></li>
                                    <li><a href="{{store direct_url='customer/account'}}">Twoje Konto</a></li>
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

foreach ($cmsNavigationBlocks as $blockData) {
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

