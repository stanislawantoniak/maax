<?php

$blocks = array(
    array(
        'title' => 'Nawigacja rozwijana, kategoria DZIEWCZYNKA',
        'identifier' => 'navigation-dropdown-c-4',
        'content'       =>
            <<<EOD
<ul class="menu-3columns">
    <li>
        <div class="clearfix">
            <div class="col-md-9 menus-column">
                <div class="box col-sm-3 col-xs-6 col-md-4 col-lg-3">
                    <dl>
                        <dt><a href="#Rozmiary" rel="category" data-description="Rozmiary">Rozmiary</a></dt>
                        <dd><a href="" data-description="62 - 86 cm">62 - 86 cm</a></dd>
                        <dd><a href="92 - 122 cm" data-description="92 - 122 cm">92 - 122 cm</a></dd>
                        <dd><a href="128 - 158 cm" data-description="128 - 158 cm">128 - 158 cm</a></dd>
                    </dl>
                </div>
                <div class="box col-sm-3 col-xs-6 col-md-4 col-lg-3">
                    <dl>
                        <dt><a href="#KATEGORIA" rel="category" data-description="KATEGORIA">KATEGORIA</a></dt>
                        <dd><a href="/bluzy" data-description="Bluzy">Bluzy</a></dd>
                        <dd><a href="/sukienki" data-description="Sukienki">Sukienki</a></dd>
                        <dd><a href="/spodniczki" data-description="Spódnice">Spódnice</a></dd>
                        <dd><a href="/" data-description="Spodnie, szorty">Spodnie, szorty</a></dd>
                        <dd><a href="/" data-description="Kombinezony">Kombinezony</a></dd>
                        <dd><a href="/" data-description="Kurtki, płaszcze">Kurtki, płaszcze</a></dd>
                    </dl>
                </div>
                <div class="box col-sm-3 col-xs-6 col-md-4 col-lg-3">
                    <dl>
                        <dt><a href="#Kolekcje" rel="category" data-description="Kolekcje">Kolekcje</a></dt>
                        <dd><a href="/" data-description="BĄBELKI">BĄBELKI <span class="menu-label">NOWOŚĆ</span></a></dd>
                        <dd><a href="/" data-description="CEREMONY - POWIEW">CEREMONY - POWIEW</a></dd>
                        <dd><a href="/" data-description="CIASTECZKOWY DZIEŃ">CIASTECZKOWY DZIEŃ</a></dd>
                        <dd><a href="/" data-description="CZAS ROZKWITU">CZAS ROZKWITU <span class="menu-label">NOWOŚĆ</span></a></dd>
                        <dd><a href="/" data-description="DIAMENT">DIAMENT</a></dd>
                        <dd><a href="/" data-description="DZIEWCZYNA Z GWIAZDĄ">DZIEWCZYNA Z GWIAZDĄ</a></dd>
                    </dl>
                </div>
            </div>
            <div class="col-md-3 media-column hidden-xs hidden-sm hidden-xs hidden-sm">
                <img src="/skin/frontend/modago/wf/images/wojcik-menu-dziewczynka.png" alt=""/>
            </div>
        </div>
    </li>
</ul>
EOD
,
        'is_active'     => 1,
        'stores'        => 0
    ),
    array(
        'title' => 'Nawigacja rozwijana, kategoria Chłopiec',
        'identifier' => 'navigation-dropdown-c-9',
        'content'       =>
            <<<EOD
<ul class="menu-3columns">
    <li>
        <div class="clearfix">
            <div class="col-md-9 menus-column">
                <div class="box col-sm-3 col-xs-6 col-md-4 col-lg-3">
                    <dl>
                        <dt><a href="#Rozmiary" rel="category" data-description="Rozmiary">Rozmiary</a></dt>
                        <dd><a href="" data-description="62 - 86 cm">62 - 86 cm</a></dd>
                        <dd><a href="92 - 122 cm" data-description="92 - 122 cm">92 - 122 cm</a></dd>
                        <dd><a href="128 - 158 cm" data-description="128 - 158 cm">128 - 158 cm</a></dd>
                    </dl>
                </div>
                <div class="box col-sm-3 col-xs-6 col-md-4 col-lg-3">
                    <dl>
                        <dt><a href="#KATEGORIA" rel="category" data-description="KATEGORIA">KATEGORIA</a></dt>
                        <dd><a href="/bluzy" data-description="Bluzy">Bluzy</a></dd>
                        <dd><a href="/" data-description="Spodnie, szorty">Spodnie, szorty</a></dd>
                        <dd><a href="/" data-description="Kombinezony">Kombinezony</a></dd>
                        <dd><a href="/" data-description="Czapki, szaliki">Czapki, szaliki</a></dd>
                        <dd><a href="/" data-description="Kurtki, płaszcze">Kurtki, płaszcze</a></dd>
                    </dl>
                </div>
                <div class="box col-sm-3 col-xs-6 col-md-4 col-lg-3">
                    <dl>
                        <dt><a href="#Kolekcje" rel="category" data-description="Kolekcje">Kolekcje</a></dt>
                        <dd><a href="/" data-description="BEZ ZASAD">BEZ ZASAD <span class="menu-label">NOWOŚĆ</span></a></dd>
                        <dd><a href="/" data-description="CZAS SPORTU">CZAS SPORTU</a></dd>
                        <dd><a href="/" data-description="CZTERY ŁAPY">CZTERY ŁAPY</a></dd>
                        <dd><a href="/" data-description="ELEGANCIK">ELEGANCIK <span class="menu-label">NOWOŚĆ</span></a></dd>
                        <dd><a href="/" data-description="NA MORZU">NA MORZU</a></dd>
                        <dd><a href="/" data-description="NA ROGU ULICY">NA ROGU ULICY</a></dd>
                    </dl>
                </div>
            </div>
            <div class="col-md-3 media-column hidden-xs hidden-sm hidden-xs hidden-sm">
                <img src="/skin/frontend/modago/wf/images/wojcik-menu-chlopiec.png" alt=""/>
            </div>
        </div>
    </li>
</ul>
EOD
    ,
        'is_active'     => 1,
        'stores'        => 0
    )
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

