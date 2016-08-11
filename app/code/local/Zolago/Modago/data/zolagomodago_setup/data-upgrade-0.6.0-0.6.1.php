<?php

$cms = array(

    array(
        'title'         => 'Help page',
        'identifier'    => 'help-page',
        'content'       =>
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
        'is_active'     => 1,
        'stores'        => 0
    )

);

foreach ($cms as $data) {
	$block = Mage::getModel('cms/block')->load($data['identifier']);
	if ($block->getBlockId()) {
		$oldData = $block->getData();
		$data = array_merge($oldData,$data);
	}

	$block->setData($data)->save();
}

