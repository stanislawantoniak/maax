<?php
//remove old blocks (just to be sure that everything is set up right
$blocksToRemove = Mage::getModel('cms/block')->getCollection();
$blocksToRemove->addFieldToFilter("identifier", array('in' => array("help-page","help-page-mobile-menu")));
foreach($blocksToRemove as $blockToRemove) {
	$blockToRemove->delete();
}

//recreate blocks with correct scopes
$allStores = 0;
$modagoStore =  Mage::app()->getStore('default')->getId();

$blocksToCreate = array(
	array(
		'title' => 'Help page (Modago.pl)',
		'identifier' => 'help-page',
		'content' =>
			<<<EOD
{{block type="cms/block" block_id="help-page-mobile-menu"}}
<div class="container-fluid">
	<div id="help-content">
		<div class="row">
			<div class="col-xs-6 help-tile">
				<a href="{{store url='faq'}}" class="help-tile-container">
					<div class="help-tile-content help-tile-faq help-tile-long-title">
						<div class="help-tile-title">
							ODPOWIEDZI NA
							<span class="help-tile-title-divider"></span>
							NAJCZĘSTSZE PYTANIA
						</div>
						<ul class="help-tile-list">
							<li>koszty i sposoby dostawy</li>
							<li>formy płatności</li>
							<li>dostępność produktów</li>
							<li>rozmiary produktów</li>
						</ul>
					</div>
				</a>
			</div>
			<div class="col-xs-6 help-tile">
				<a href="{{store url='sales/order/process'}}" class="help-tile-container">
					<div class="help-tile-content help-tile-orders">
						<div class="help-tile-title">
							TWOJE ZAMÓWIENIA
						</div>
						<ul class="help-tile-list">
							<li>sprawdź stan realizacji zamówienia</li>
							<li>dowiedz się gdzie jest paczka</li>
							<li>zadaj pytanie dotyczące zamówienia</li>
							<li>zobacz historię zamówień</li>
						</ul>
					</div>
				</a>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-6 help-tile">
				<a href="{{store url='sales/po/rmalist'}}" class="help-tile-container">
					<div class="help-tile-content help-tile-rma help-tile-long-title">
						<div class="help-tile-title">
							ZGŁOŚ ZWROT
							<span class="help-tile-title-divider"></span>
							LUB REKLAMACJĘ
						</div>
						<ul class="help-tile-list">
							<li>skorzystaj z darmowego zwrotu</li>
							<li>zgłoś reklamację</li>
							<li>wymień produkt na inny rozmiar</li>
						</ul>
					</div>
				</a>
			</div>
			<div class="col-xs-6 help-tile">
				<a href="{{store url='help/contact/vendor'}}" class="help-tile-container">
					<div class="help-tile-content help-tile-contact">
						<div class="help-tile-title">
							KONTAKT
						</div>
						<ul class="help-tile-list">
							<li>zadaj pytanie sprzedawcy</li>
							<li>skontaktuj się ze sklepem</li>
							<li>zgłoś uwagę lub sugestię zmian</li>
						</ul>
					</div>
				</a>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-6 help-tile">
				<a href="{{store url='mypromotions'}}" class="help-tile-container help-tile-container-promo">
					<div class="help-tile-content help-tile-promo">
						<div class="help-tile-title">
							SPRAWDŹ KORZYŚCI
						</div>
						<div class="help-tile-subtitle">
							ZAŁÓŻ KONTO I ODKRYJ SPECJALNE RABATY
						</div>
						<div class="help-tile-text">
							Im więcej przeglądasz, im więcej kupujesz, tym lepiej dobierzemy dla Ciebie kupony rabatowe.
						</div>
					</div>
				</a>
			</div>
			<div class="col-xs-6 help-tile">
				<a href="{{store url='help/contact/gallery'}}" class="help-tile-container">
					<div class="help-tile-content help-tile-sellers">
						<div class="help-tile-title">
							DLA SPRZEDAWCÓW
						</div>
						<ul class="help-tile-list help-tile-list-nopointers">
							<li>Reprezentujesz sklep z branży modowej?</li>
							<li>Interesuje Cię współpraca?</li>
							<li>Napisz do nas!</li>
						</ul>
					</div>
				</a>
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
		'title' => 'Help page (default)',
		'identifier' => 'help-page',
		'content' =>
			<<<EOD
{{block type="cms/block" block_id="help-page-mobile-menu"}}
<div class="container-fluid">
	<div id="help-content">
		<div class="row">
			<div class="col-xs-6 help-tile">
				<a href="{{store url='faq'}}" class="help-tile-container">
					<div class="help-tile-content help-tile-faq help-tile-long-title">
						<div class="help-tile-title">
							ODPOWIEDZI NA
							<span class="help-tile-title-divider"></span>
							NAJCZĘSTSZE PYTANIA
						</div>
						<ul class="help-tile-list">
							<li>koszty i sposoby dostawy</li>
							<li>formy płatności</li>
							<li>dostępność produktów</li>
							<li>rozmiary produktów</li>
						</ul>
					</div>
				</a>
			</div>
			<div class="col-xs-6 help-tile">
				<a href="{{store url='sales/order/process'}}" class="help-tile-container">
					<div class="help-tile-content help-tile-orders">
						<div class="help-tile-title">
							TWOJE ZAMÓWIENIA
						</div>
						<ul class="help-tile-list">
							<li>sprawdź stan realizacji zamówienia</li>
							<li>dowiedz się gdzie jest paczka</li>
							<li>zadaj pytanie dotyczące zamówienia</li>
							<li>zobacz historię zamówień</li>
						</ul>
					</div>
				</a>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-6 help-tile">
				<a href="{{store url='sales/po/rmalist'}}" class="help-tile-container">
					<div class="help-tile-content help-tile-rma help-tile-long-title">
						<div class="help-tile-title">
							ZGŁOŚ ZWROT
							<span class="help-tile-title-divider"></span>
							LUB REKLAMACJĘ
						</div>
						<ul class="help-tile-list">
							<li>skorzystaj z darmowego zwrotu</li>
							<li>zgłoś reklamację</li>
							<li>wymień produkt na inny rozmiar</li>
						</ul>
					</div>
				</a>
			</div>
			<div class="col-xs-6 help-tile">
				<a href="{{store url='contact'}}" class="help-tile-container">
					<div class="help-tile-content help-tile-contact">
						<div class="help-tile-title">
							KONTAKT
						</div>
						<ul class="help-tile-list">
							<li>zadaj pytanie sprzedawcy</li>
							<li>skontaktuj się ze sklepem</li>
							<li>zgłoś uwagę lub sugestię zmian</li>
						</ul>
					</div>
				</a>
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
		'title' => 'Help page mobile menu (Modago.pl)',
		'identifier' => 'help-page-mobile-menu',
		'content' =>
			<<<EOD
<div id="help-mobile-menu">
    <div class="wrapp-section bg-w visible-xs">
        <h1><a href="{{store url='help'}}">Pomoc</a></h1>
    </div>
</div>
EOD
	,
		'is_active' => 1,
		'stores' => $modagoStore
	),
	array(
		'title' => 'Help page mobile menu (default)',
		'identifier' => 'help-page-mobile-menu',
		'content' =>
			<<<EOD
<div id="help-mobile-menu">
    <div class="wrapp-section bg-w visible-xs">
        <h1><a href="{{store url='help'}}">Pomoc</a></h1>
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