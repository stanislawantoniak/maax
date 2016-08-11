<?php
$page = Mage::getModel('cms/page')->getCollection()->addFieldToFilter('identifier', 'informacje-o-modago.html')->getFirstItem();

if($page && $page->getId()) {

	$page->setContent(<<<EOD
<div id="about" class="container-fluid bg-w">
	<div class="about-header">
		<div class="about-header-title">Czym jest <span class="about-header-logo">MODAGO?</span></div>
		<div class="about-header-subtitle">Internetową galerią handlową z&nbsp;najlepszymi sklepami modowymi</div>
	</div>
	<ul class="about-list">
		<li class="about-list-item about-heart">
			<div class="about-list-header">
				Twoje ulubione sklepy modowe dostępne w&nbsp;jednym miejscu
			</div>
			<div class="about-list-text">
				Dopiero ruszyliśmy, ale&nbsp;już niebawem znajdziesz u&nbsp;nas pełne kolekcje swoich ulubionych marek,
				od&nbsp;najpopularniejszych sklepów, które znasz ze&nbsp;stacjonarnych galerii handlowych.
			</div>
		</li>
		<li class="about-list-item about-shops">
			<div class="about-list-header">
				Wiele sklepów, jeden koszyk zakupowy
			</div>
			<div class="about-list-text">
				Przejrzysz szybko ofertę wielu sklepów i&nbsp;łatwo znajdziesz coś dla siebie. Produkty wrzucisz
				do&nbsp;jednego koszyka zakupowego i&nbsp;kupisz w&nbsp;kilka klików.
			</div>
		</li>
		<li class="about-list-item about-promotions">
			<div class="about-list-header">
				Specjalne promocje dla&nbsp;naszych Klientów
			</div>
			<div class="about-list-text">
				Zakładając konto w&nbsp;galerii otrzymasz dostęp do&nbsp;specjalnych promocji, niedostępnych nigdzie
				indziej. Dołącz do&nbsp;nas i&nbsp;korzystaj z&nbsp;dodatkowych rabatów.
			</div>
		</li>
		<li class="about-list-item about-free-delivery">
			<div class="about-list-header">
				Darmowa dostawa na&nbsp;99% produktów
			</div>
			<div class="about-list-text">
				Kupując u&nbsp;nas masz gwarancję szybkiej wysyłki oraz darmową dostawę na&nbsp;większość produktów.
				Bezpieczną dostawę gwarantują sprawdzone firmy kurierskie.
			</div>
		</li>
		<li class="about-list-item about-quick-delivery">
			<div class="about-list-header">
				Szybka dostawa
			</div>
			<div class="about-list-text">
				Produkty otrzymasz w&nbsp;ciągu jednego do&nbsp;dwóch dni. Na&nbsp;Modago oferowane są&nbsp;tylko produkty,
				które Sprzedawcy posiadają na&nbsp;magazynie i&nbsp;są&nbsp;je&nbsp;w&nbsp;stanie wysłać w&nbsp;ciągu maksymalnie&nbsp;24h.
			</div>
		</li>
		<li class="about-list-item about-return">
			<div class="about-list-header">
				Łatwy i&nbsp;darmowy zwrot w&nbsp;ciągu 30&nbsp;dni
			</div>
			<div class="about-list-text">
				Jeśli się&nbsp;rozmyślisz, produkty możesz łatwo zwrócić wypełniając prosty formularz na&nbsp;naszej
				stronie. Każdy sprzedawca w&nbsp;Modago oferuje możliwość zwrotu w&nbsp;ciągu co&nbsp;najmniej 30&nbsp;dni.
			</div>
		</li>
	</ul>
	<div class="about-footer">
		<div class="about-footer-header">
			Już wkrótce nowe sklepy, aby&nbsp;każdy mógł znaleźć swoje ulubione marki!
		</div>
		<div class="about-footer-text">
			Chcesz dowiedzieć się&nbsp;więcej? Masz jakieś pytania?
			<a href="#">Zobacz&nbsp;odpowiedzi&nbsp;na&nbsp;najczęściej&nbsp;zadawane&nbsp;pytania.</a>
		</div>
	</div>
</div>

{{block type="zolagomodago/about_register" template="about/register.phtml"}}
EOD
	);

	$page->setStores(array(0)); //sets it to all stores, if I don't set it here then it automatically unsets all stores. Don't know why ;)

	$page->save();
}


$cmsNavigationBlocks = array(
	array(
		'title'         => 'Informacje o Modago nagłówek rejestracji',
		'identifier'    => 'about-register-header',
		'content'       => <<<EOD
		<div class="about-register-header">
			<div class="about-register-header-title">Twoje ulubione marki do <span class="about-register-bold">50%&nbsp;taniej!</span></div>
			<div class="about-register-header-subtitle">Załóż konto i&nbsp;odkryj specjalne rabaty.</div>
			<div class="about-register-header-text">
				Im&nbsp;więcej przeglądasz, im&nbsp;więcej kupujesz ty&nbsp;lepiej dobierzemy dla&nbsp;Ciebie kupony rabatowe.<br/>
				Zarejestruj&nbsp;się, wyraź zgodę na&nbsp;mailing i&nbsp;odbierz swoje pierwsze kupony.
			</div>
		</div>
EOD
	,
		'is_active'     => 1,
		'stores'        => 0
	)
);

foreach ($cmsNavigationBlocks as $data) {
	$block = Mage::getModel('cms/block')->load($data['identifier']);
	if ($block->getBlockId()) {
		$oldData = $block->getData();
		$data = array_merge($oldData,$data);
	}
	$block->setData($data)->save();
}