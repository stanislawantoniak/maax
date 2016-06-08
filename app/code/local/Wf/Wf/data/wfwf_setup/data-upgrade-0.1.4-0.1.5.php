<?php

$blocks = array(
    array(
        'title' => 'Help page (default)',
        'identifier' => 'help-page',
        'content'       =>
            <<<EOD
{{block type="cms/block" block_id="help-page-mobile-menu"}}
<div class="container-fluid">
    <div id="help-content-2">
        <div class="row">
            <a href="{{store url='faq'}}" class="icon-box  left-icon design-2 animation-2 col-sm-6 col-xs-6">
                <div class="help-item">
                    <div class="icon col-sm-3 col-xs-12">
                        <i class="fa fa-question" style=""></i>
                    </div>
                    <div class="icon-content col-sm-8">
                        <h3>ODPOWIEDZI NA NAJCZĘSTSZE PYTANIA</h3>
                        <hr class="divider hidden-xs">
                        <div class="icon-text hidden-xs">
                            <ul class="help-tile-list">
                                <li>koszty i sposoby dostawy</li>
                                <li>formy płatności</li>
                                <li>dostępność produktów</li>
                                <li>rozmiary produktów</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </a>
            <a href="{{store url='sales/order/process'}}" class="icon-box  left-icon design-2 animation-2 col-sm-6 col-xs-6">
                <div class="help-item">
                    <div class="icon col-sm-3 col-xs-12">
                        <i class="fa fa-list-alt" style=""></i>
                    </div>
                    <div class="icon-content col-sm-8">
                        <h3>TWOJE ZAMÓWIENIA</h3>
                        <hr class="divider hidden-xs">
                        <div class="icon-text hidden-xs">
                            <ul class="help-tile-list">
                                <li>sprawdź stan realizacji zamówienia</li>
                                <li>dowiedz się gdzie jest paczka</li>
                                <li>zadaj pytanie dotyczące zamówienia</li>
                                <li>zobacz historię zamówień</li>
                            </ul>
                        </div>
                    </div>
                </div>

            </a>
        </div>
        <div class="row">
            <a href="{{store url='zwroty-i-reklamacje'}}" class="icon-box  left-icon design-2 animation-2 col-sm-6 col-xs-6">
                <div class="help-item">
                    <div class="icon col-sm-3 col-xs-12">
                        <i class="fa fa-repeat" style=""></i>
                    </div>
                    <div class="icon-content col-sm-8">
                        <h3>ZGŁOŚ ZWROT LUB REKLAMACJĘ</h3>
                        <hr class="divider hidden-xs">
                        <div class="icon-text hidden-xs">
                            <ul class="help-tile-list">
                                <li>skorzystaj z darmowego zwrotu</li>
                                <li>zgłoś reklamację</li>
                                <li>wymień produkt na inny rozmiar</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </a>
            <a href="{{store url='help/contact'}}" class="icon-box  left-icon design-2 animation-2 col-sm-6 col-xs-6">
                <div class="help-item">
                    <div class="icon col-sm-3 col-xs-12">
                        <i class="fa fa-envelope-o" style=""></i>
                    </div>
                    <div class="icon-content col-sm-8">
                        <h3>KONTAKT</h3>
                        <hr class="divider hidden-xs">
                        <div class="icon-text hidden-xs">
                            <ul class="help-tile-list">
                                <li>zadaj pytanie sprzedawcy</li>
                                <li>skontaktuj się ze sklepem</li>
                                <li>zgłoś uwagę lub sugestię zmian</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>
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

