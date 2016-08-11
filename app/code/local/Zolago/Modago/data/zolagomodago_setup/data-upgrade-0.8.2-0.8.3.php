<?php
//scopes
$allStores = 0;
$modagoStore =  Mage::app()->getStore('default')->getId();

$pagesToCreate = array(
    array(
        'title' => 'Zwroty i reklamacje',
        'identifier' => 'zwroty-i-reklamacje',
        'content' =>
            <<<EOD
<section id="rma-page" class="cms-section">
	<div class="rma-page-header">
		<div class="rma-page-header1">Każdy produkt możesz zwrócić w&nbsp;ciągu 30&nbsp;dni, bez&nbsp;podania&nbsp;przyczyny.</div>
		<div class="rma-page-header2">Produkty zwracasz&nbsp;w <span class="bigger">100%&nbsp;ZA&nbsp;DARMO!</span></div>
	</div>
	<div class="benefits">
		<div class="benefit-item">
			<div class="benefit-text benefit-text-rma" style="background-image: url('/skin/frontend/modago/gallery/images/svg/notebook.svg')">
				Zgłaszasz zwrot lub reklamację w zaledwie kilku klikach.
			</div>
		</div>
		<div class="benefit-item">
			<div class="benefit-text benefit-text-rma" style="background-image: url('/skin/frontend/modago/gallery/images/svg/help_orders.svg')">
				Na bieżąco masz wgląd w status swojego zgłoszenia.
			</div>
		</div>
		<div class="benefit-item">
			<div class="benefit-text benefit-text-rma" style="background-image: url('/skin/frontend/modago/gallery/images/svg/about_delivery_quick.svg')">
				Szybko zamawiasz kuriera przez stronę Modago.pl.
			</div>
		</div>
		<div class="benefit-item">
			<div class="benefit-text benefit-text-rma" style="background-image: url('/skin/frontend/modago/gallery/images/svg/wallet.svg')">
				Błyskawicznie otrzymujesz zwrot płatności.
			</div>
		</div>
	</div>
	<div class="rma-page-footer">
		<a class="button button-primary" href="{{store url='sales/rma/history'}}">Zgłoś zwrot lub reklamację przez swoje konto klienta</a>
		<p>
			Jeśli nie masz konta, wystarczy założyć je na adres, z którego składane było zamówienie,<br />
			i na stronie &bdquo;Moje konto&rdquo; zgodzić się na podpięcie zamówień składanych bez rejestracji.
			<a href="{{store url='customer/account/create'}}">Zarejestruj&nbsp;się.</a>
		</p>
	</div>
</section>
<section class="cms-section">
	<header>
		<h2 class="title-section">
			Chcesz zwrócić produkt?
		</h2>
	</header>
	<p>
		Każdy produkt zakupiony w galerii możesz zwrócić w ciągu 14 dni od jego odbioru. Niektóre sklepy dają na to więcej czasu – informację
		na ten temat zawsze znajdziesz w opisie produktu i sprzedawcy. Zwrot jest bezpłatny, a cały proces można monitorować z panelu Klienta.
	</p>
	<p>
		Zwracane produkty nie mogą być używane. Radzimy więc ostrożnie przymierzać ubrania, a buty sprawdzać na miękkim podłożu (np. dywanie).
		Uważaj, aby nie usuwać przyczepionych metek ani nie ubrudzić artykułów.
	</p>
	<p>
		Aby zwrócić produkt:
	</p>
	<ul>
		<li>Zaloguj się do swojego konta w serwisie.</li>
		<li>Wejdź w zakładkę „Zwroty i reklamacje”.</li>
		<li>Kliknij w przycisk „Zgłoś zwrot lub reklamację”.</li>
		<li>Wybierz zamówienie z produktami, które chcesz zwrócić.</li>
		<li>Wskaż produkt, który chcesz zwrócić i podaj przyczynę zwrotu.</li>
		<li>Zamów kuriera wskazując adres i wybierając termin odbioru przesyłki.</li>
		<li>Sprawdź jeszcze raz dane. Jeśli są poprawne, wyślij zgłoszenie.</li>
		<li>Postępuj zgodnie z instrukcją widoczną na ekranie. Wydrukuj plik PDF
			zawierający formularz zwrotu i listy przewozowe dla kuriera (2 strony).</li>
		<li>Włóż oryginalnie zapakowany produkt wraz z formularzem zwrotu do kartonu przeznaczonego do wysyłki
			i dobrze go zaklej. Możesz wykorzystać opakowanie, w którym przyszły zamówione artykuły.</li>
		<li>Przekaż w ustalonym terminie paczkę wraz z listami przewozowymi dla kuriera (2 strony).</li>
	</ul>
	<p>
		Status zwrotu możesz na bieżąco kontrolować wchodząc w zakładkę „Zwroty i reklamacje” w swoim koncie.
	</p>
	<p>
		Pieniądze zostaną zwrócone w ciągu 14 dni po otrzymaniu przesyłki zwrotnej przez sklep. Zazwyczaj trwa to jednak krócej.
		Forma zwrotu płatności zależy od metody płatności wybranej podczas składania zamówienia:
	</p>
	<ul>
		<li>W przypadku płatności przelewem, wartość zwracanych artykułów zostanie przelana na konto, z którego dokonano płatności.</li>
		<li>W przypadku płatności za pobraniem, wartość zwracanych artykułów zostanie przelana na konto podane podczas zgłaszania zwrotu.</li>
		<li>W przypadku płatności kartą kredytową, kwota zostanie przelana na konto Twojej karty kredytowej.</li>
	</ul>
</section>
<section class="cms-section">
	<header>
		<h2 class="title-section">
			Chcesz wymienic na inny rozmiar?
		</h2>
	</header>
	<p>
		Wymiana towaru kupionego w naszej galerii polega na zwrocie wybranych artykułów i złożeniu kolejnego zamówienia.
		Jeśli zamówiony produkt okazał się np. za mały, skorzystaj z procedury zwrotu. Każdy produkt zakupiony w galerii
		możesz zwrócić w ciągu 14 dni od jego odbioru. Niektóre sklepy dają na to więcej czasu – informację na ten temat
		zawsze znajdziesz w opisie produktu i sprzedawcy. Więcej informacji znajdziesz powyżej, w temacie "Zwroty towaru"
		oraz na stronie Zwroty i reklamacje. W każdej chwili możesz zamówić wybrany artykuł w innym rozmiarze lub kolorze.
		Przesyłka zwrotna jest bezpłatna, a pieniądze zwracane są jak najszybciej, nie później niż w przeciągu 14 dni.
	</p>
</section>
<section class="cms-section">
	<header>
		<h2 class="title-section">
			Chcesz zareklamować produkt?
		</h2>
	</header>
	<p>
		Na wszystkie produkty kupione w sklepach, w naszej galerii przysługują Ci uprawnienia z tytułu niezgodności towaru
		z umową, przez okres 2 lat od daty zakupu, zgodnie z ustawą z dnia 27 lipca 2002 r. o szczególnych warunkach sprzedaży
		konsumenckiej oraz o zmianie Kodeksu cywilnego (Dz.U. z 2002 r. Nr 141 z późn. zm.).
	</p>
	<p>
		Reklamacji podlegają wady ukryte powstałe z winy producenta.
	</p>
	<p>
		Reklamacji nie podlegają:
	</p>
	<ul>
		<li>naturalne zużywanie się obuwia,</li>
		<li>części wymienne np. fleki czy sznurówki,</li>
		<li>uszkodzenia mechaniczne (otarcie, rozerwanie, naderwanie bądź zadrapanie, oderwanie ozdób, zamka, gumy itp.),
			oraz uszkodzenia powstałe w wyniku braku lub nieprawidłowej konserwacji,</li>
		<li>uszkodzenia powstałe na skutek użytkowania obuwia i odzieży niezgodnie z przeznaczeniem,</li>
		<li>przebarwienia odzieży lub stóp przez obuwie wykonane na naturalnie barwionej podszewce,</li>
		<li>wygoda obuwia, przemakanie obuwia (o ile w opisie nie jest napisane, iż towar jest wodoodporny).</li>
	</ul>
	<p>
		O niezgodności towaru z umową musisz poinformować sprzedawcę najpóźniej w terminie dwóch miesięcy od stwierdzenia
		tejże niezgodności. Po stwierdzeniu niezgodności nie można użytkować uszkodzonego towaru.
	</p>
	<p>
		Produkty wadliwe lub niekompletne możesz zareklamować u sprzedawcy korzystając z naszego panelu.
	</p>
	<p>
		Aby zgłosić reklamację produktu:
	</p>
	<ul>
		<li>Zaloguj się do swojego konta w serwisie.</li>
		<li>Wejdź w zakładkę „Zwroty i reklamacje”.</li>
		<li>Kliknij w przycisk „Zgłoś zwrot lub reklamację”.</li>
		<li>Wybierz zamówienie z produktem, który chcesz zareklamować.</li>
		<li>Wskaż produkt, który chcesz zareklamować i wybierz z listy jako przyczynę zwrotu „Reklamacja”.</li>
		<li>W polu „Dodatkowe informacje dotyczące zgłoszenia” opisz powód dlaczego reklamujesz produkt.</li>
		<li>Sprawdź jeszcze raz dane. Jeśli są poprawne, wyślij zgłoszenie.</li>
	</ul>
	<p>
		Otrzymasz potwierdzenie przyjęcia zgłoszenia przez sprzedawcę. Gdy sklep zapozna się ze sprawą skontaktuje się z Tobą
		z potwierdzeniem realizacji reklamacji lub prośbą o dodatkowe informacje. Jeśli reklamacja zostanie przez sklep przyjęta,
		otrzymasz dalsze wskazówki odesłania produktu.
	</p>
	<p>Aby odesłać produkt:</p>
	<ul>
		<li>Kliknij w link w mailu z informacją o potwierdzeniu realizacji reklamacji.</li>
		<li>Zamów kuriera wskazując adres i wybierając termin odbioru przesyłki.</li>
		<li>Sprawdź jeszcze raz dane. Jeśli są poprawne, wyślij zgłoszenie.</li>
		<li>Postępuj zgodnie z instrukcją widoczną na ekranie. Wydrukuj plik PDF zawierający formularz zwrotu
			i listy przewozowe dla kuriera (2 strony).</li>
		<li>Włóż oryginalnie zapakowany produkt wraz z formularzem zwrotu do kartonu przeznaczonego do wysyłki
			i dobrze go zaklej. Możesz wykorzystać opakowanie, w którym przyszły zamówione artykuły.</li>
		<li>Przekaż w ustalonym terminie paczkę wraz z listami przewozowymi dla kuriera (2 strony).</li>
	</ul>
	<p>
		Reklamacja zostanie rozpatrzona maksymalnie w ciągu 14 dni od daty otrzymania reklamowanego artykułu.
		W przypadku uznania reklamacji otrzymasz częściowy lub całkowity zwrot zapłaconej kwoty w zależności od skali uszkodzenia.
		Status realizacji reklamacji możesz na bieżąco kontrolować wchodząc w zakładkę „Zwroty i reklamacje” w swoim koncie.
	</p>
	<p>
		Jeśli zostało ustalone ze sklepem, że otrzymasz zwrot pieniędzy, dostaniesz powiadomienie w momencie wykonania przelewu.
		Pieniądze zostaną zwrócone w ciągu 14 dni po otrzymaniu przesyłki przez sklep. Zazwyczaj trwa to jednak krócej.
		Forma zwrotu płatności zależy od metody płatności wybranej podczas składania zamówienia:
	</p>
	<ul>
		<li>W przypadku płatności przelewem, wartość zwracanych artykułów zostanie przelana na konto, z którego dokonano płatności.</li>
		<li>W przypadku płatności za pobraniem, wartość zwracanych artykułów zostanie przelana na konto podane podczas zgłaszania reklamacji.</li>
		<li>W przypadku płatności kartą kredytową, kwota zostanie przelana na konto Twojej karty kredytowej.</li>
	</ul>
</section>
EOD
    ,
        'root_template' => 'one_column',
        'is_active' => 1,
        'stores' => $modagoStore
    ),
    array(
        'title' => 'Zwroty i reklamacje',
        'identifier' => 'zwroty-i-reklamacje',
        'content' =>
            <<<EOD
<section id="rma-page" class="cms-section">
	<div class="rma-page-header">
		<div class="rma-page-header1">Każdy produkt możesz zwrócić w&nbsp;ciągu 30&nbsp;dni, bez&nbsp;podania&nbsp;przyczyny.</div>
		<div class="rma-page-header2">Produkty zwracasz&nbsp;w <span class="bigger">100%&nbsp;ZA&nbsp;DARMO!</span></div>
	</div>
	<div class="benefits">
		<div class="benefit-item">
			<div class="benefit-text benefit-text-rma" style="background-image: url('/skin/frontend/modago/gallery/images/svg/notebook.svg')">
				Zgłaszasz zwrot lub reklamację w zaledwie kilku klikach.
			</div>
		</div>
		<div class="benefit-item">
			<div class="benefit-text benefit-text-rma" style="background-image: url('/skin/frontend/modago/gallery/images/svg/help_orders.svg')">
				Na bieżąco masz wgląd w status swojego zgłoszenia.
			</div>
		</div>
		<div class="benefit-item">
			<div class="benefit-text benefit-text-rma" style="background-image: url('/skin/frontend/modago/gallery/images/svg/about_delivery_quick.svg')">
				Szybko zamawiasz kuriera przez stronę Modago.pl.
			</div>
		</div>
		<div class="benefit-item">
			<div class="benefit-text benefit-text-rma" style="background-image: url('/skin/frontend/modago/gallery/images/svg/wallet.svg')">
				Błyskawicznie otrzymujesz zwrot płatności.
			</div>
		</div>
	</div>
	<div class="rma-page-footer">
		<a class="button button-primary" href="{{store url='sales/rma/history'}}">Zgłoś zwrot lub reklamację przez swoje konto klienta</a>
		<p>
			Jeśli nie masz konta, wystarczy założyć je na adres, z którego składane było zamówienie,<br />
			i na stronie &bdquo;Moje konto&rdquo; zgodzić się na podpięcie zamówień składanych bez rejestracji.
			<a href="{{store url='customer/account/create'}}">Zarejestruj&nbsp;się.</a>
		</p>
	</div>
</section>
<section class="cms-section">
	<header>
		<h2 class="title-section">
			Chcesz zwrócić produkt?
		</h2>
	</header>
	<p>
		Każdy produkt zakupiony w galerii możesz zwrócić w ciągu 14 dni od jego odbioru. Niektóre sklepy dają na to więcej czasu – informację
		na ten temat zawsze znajdziesz w opisie produktu i sprzedawcy. Zwrot jest bezpłatny, a cały proces można monitorować z panelu Klienta.
	</p>
	<p>
		Zwracane produkty nie mogą być używane. Radzimy więc ostrożnie przymierzać ubrania, a buty sprawdzać na miękkim podłożu (np. dywanie).
		Uważaj, aby nie usuwać przyczepionych metek ani nie ubrudzić artykułów.
	</p>
	<p>
		Aby zwrócić produkt:
	</p>
	<ul>
		<li>Zaloguj się do swojego konta w serwisie.</li>
		<li>Wejdź w zakładkę „Zwroty i reklamacje”.</li>
		<li>Kliknij w przycisk „Zgłoś zwrot lub reklamację”.</li>
		<li>Wybierz zamówienie z produktami, które chcesz zwrócić.</li>
		<li>Wskaż produkt, który chcesz zwrócić i podaj przyczynę zwrotu.</li>
		<li>Zamów kuriera wskazując adres i wybierając termin odbioru przesyłki.</li>
		<li>Sprawdź jeszcze raz dane. Jeśli są poprawne, wyślij zgłoszenie.</li>
		<li>Postępuj zgodnie z instrukcją widoczną na ekranie. Wydrukuj plik PDF
			zawierający formularz zwrotu i listy przewozowe dla kuriera (2 strony).</li>
		<li>Włóż oryginalnie zapakowany produkt wraz z formularzem zwrotu do kartonu przeznaczonego do wysyłki
			i dobrze go zaklej. Możesz wykorzystać opakowanie, w którym przyszły zamówione artykuły.</li>
		<li>Przekaż w ustalonym terminie paczkę wraz z listami przewozowymi dla kuriera (2 strony).</li>
	</ul>
	<p>
		Status zwrotu możesz na bieżąco kontrolować wchodząc w zakładkę „Zwroty i reklamacje” w swoim koncie.
	</p>
	<p>
		Pieniądze zostaną zwrócone w ciągu 14 dni po otrzymaniu przesyłki zwrotnej przez sklep. Zazwyczaj trwa to jednak krócej.
		Forma zwrotu płatności zależy od metody płatności wybranej podczas składania zamówienia:
	</p>
	<ul>
		<li>W przypadku płatności przelewem, wartość zwracanych artykułów zostanie przelana na konto, z którego dokonano płatności.</li>
		<li>W przypadku płatności za pobraniem, wartość zwracanych artykułów zostanie przelana na konto podane podczas zgłaszania zwrotu.</li>
		<li>W przypadku płatności kartą kredytową, kwota zostanie przelana na konto Twojej karty kredytowej.</li>
	</ul>
</section>
<section class="cms-section">
	<header>
		<h2 class="title-section">
			Chcesz wymienic na inny rozmiar?
		</h2>
	</header>
	<p>
		Wymiana towaru kupionego w naszej galerii polega na zwrocie wybranych artykułów i złożeniu kolejnego zamówienia.
		Jeśli zamówiony produkt okazał się np. za mały, skorzystaj z procedury zwrotu. Każdy produkt zakupiony w galerii
		możesz zwrócić w ciągu 14 dni od jego odbioru. Niektóre sklepy dają na to więcej czasu – informację na ten temat
		zawsze znajdziesz w opisie produktu i sprzedawcy. Więcej informacji znajdziesz powyżej, w temacie "Zwroty towaru"
		oraz na stronie Zwroty i reklamacje. W każdej chwili możesz zamówić wybrany artykuł w innym rozmiarze lub kolorze.
		Przesyłka zwrotna jest bezpłatna, a pieniądze zwracane są jak najszybciej, nie później niż w przeciągu 14 dni.
	</p>
</section>
<section class="cms-section">
	<header>
		<h2 class="title-section">
			Chcesz zareklamować produkt?
		</h2>
	</header>
	<p>
		Na wszystkie produkty kupione w sklepach, w naszej galerii przysługują Ci uprawnienia z tytułu niezgodności towaru
		z umową, przez okres 2 lat od daty zakupu, zgodnie z ustawą z dnia 27 lipca 2002 r. o szczególnych warunkach sprzedaży
		konsumenckiej oraz o zmianie Kodeksu cywilnego (Dz.U. z 2002 r. Nr 141 z późn. zm.).
	</p>
	<p>
		Reklamacji podlegają wady ukryte powstałe z winy producenta.
	</p>
	<p>
		Reklamacji nie podlegają:
	</p>
	<ul>
		<li>naturalne zużywanie się obuwia,</li>
		<li>części wymienne np. fleki czy sznurówki,</li>
		<li>uszkodzenia mechaniczne (otarcie, rozerwanie, naderwanie bądź zadrapanie, oderwanie ozdób, zamka, gumy itp.),
			oraz uszkodzenia powstałe w wyniku braku lub nieprawidłowej konserwacji,</li>
		<li>uszkodzenia powstałe na skutek użytkowania obuwia i odzieży niezgodnie z przeznaczeniem,</li>
		<li>przebarwienia odzieży lub stóp przez obuwie wykonane na naturalnie barwionej podszewce,</li>
		<li>wygoda obuwia, przemakanie obuwia (o ile w opisie nie jest napisane, iż towar jest wodoodporny).</li>
	</ul>
	<p>
		O niezgodności towaru z umową musisz poinformować sprzedawcę najpóźniej w terminie dwóch miesięcy od stwierdzenia
		tejże niezgodności. Po stwierdzeniu niezgodności nie można użytkować uszkodzonego towaru.
	</p>
	<p>
		Produkty wadliwe lub niekompletne możesz zareklamować u sprzedawcy korzystając z naszego panelu.
	</p>
	<p>
		Aby zgłosić reklamację produktu:
	</p>
	<ul>
		<li>Zaloguj się do swojego konta w serwisie.</li>
		<li>Wejdź w zakładkę „Zwroty i reklamacje”.</li>
		<li>Kliknij w przycisk „Zgłoś zwrot lub reklamację”.</li>
		<li>Wybierz zamówienie z produktem, który chcesz zareklamować.</li>
		<li>Wskaż produkt, który chcesz zareklamować i wybierz z listy jako przyczynę zwrotu „Reklamacja”.</li>
		<li>W polu „Dodatkowe informacje dotyczące zgłoszenia” opisz powód dlaczego reklamujesz produkt.</li>
		<li>Sprawdź jeszcze raz dane. Jeśli są poprawne, wyślij zgłoszenie.</li>
	</ul>
	<p>
		Otrzymasz potwierdzenie przyjęcia zgłoszenia przez sprzedawcę. Gdy sklep zapozna się ze sprawą skontaktuje się z Tobą
		z potwierdzeniem realizacji reklamacji lub prośbą o dodatkowe informacje. Jeśli reklamacja zostanie przez sklep przyjęta,
		otrzymasz dalsze wskazówki odesłania produktu.
	</p>
	<p>Aby odesłać produkt:</p>
	<ul>
		<li>Kliknij w link w mailu z informacją o potwierdzeniu realizacji reklamacji.</li>
		<li>Zamów kuriera wskazując adres i wybierając termin odbioru przesyłki.</li>
		<li>Sprawdź jeszcze raz dane. Jeśli są poprawne, wyślij zgłoszenie.</li>
		<li>Postępuj zgodnie z instrukcją widoczną na ekranie. Wydrukuj plik PDF zawierający formularz zwrotu
			i listy przewozowe dla kuriera (2 strony).</li>
		<li>Włóż oryginalnie zapakowany produkt wraz z formularzem zwrotu do kartonu przeznaczonego do wysyłki
			i dobrze go zaklej. Możesz wykorzystać opakowanie, w którym przyszły zamówione artykuły.</li>
		<li>Przekaż w ustalonym terminie paczkę wraz z listami przewozowymi dla kuriera (2 strony).</li>
	</ul>
	<p>
		Reklamacja zostanie rozpatrzona maksymalnie w ciągu 14 dni od daty otrzymania reklamowanego artykułu.
		W przypadku uznania reklamacji otrzymasz częściowy lub całkowity zwrot zapłaconej kwoty w zależności od skali uszkodzenia.
		Status realizacji reklamacji możesz na bieżąco kontrolować wchodząc w zakładkę „Zwroty i reklamacje” w swoim koncie.
	</p>
	<p>
		Jeśli zostało ustalone ze sklepem, że otrzymasz zwrot pieniędzy, dostaniesz powiadomienie w momencie wykonania przelewu.
		Pieniądze zostaną zwrócone w ciągu 14 dni po otrzymaniu przesyłki przez sklep. Zazwyczaj trwa to jednak krócej.
		Forma zwrotu płatności zależy od metody płatności wybranej podczas składania zamówienia:
	</p>
	<ul>
		<li>W przypadku płatności przelewem, wartość zwracanych artykułów zostanie przelana na konto, z którego dokonano płatności.</li>
		<li>W przypadku płatności za pobraniem, wartość zwracanych artykułów zostanie przelana na konto podane podczas zgłaszania reklamacji.</li>
		<li>W przypadku płatności kartą kredytową, kwota zostanie przelana na konto Twojej karty kredytowej.</li>
	</ul>
</section>
EOD
    ,
        'root_template' => 'one_column',
        'is_active' => 1,
        'stores' => $allStores
    )
);

foreach ($pagesToCreate as $pageData) {
    $collection = Mage::getModel('cms/page')->getCollection();
    $collection->addStoreFilter($pageData['stores']);
    $collection->addFieldToFilter('identifier',$pageData["identifier"]);
    $currentPage = $collection->getFirstItem();

    if ($currentPage->getId()) {
        $oldBlock = $currentPage->getData();
        $pageData = array_merge($oldBlock, $pageData);
    }

    $currentPage->setData($pageData)->save();
}

$blocksToCreate = array(
    array(
        'title' => 'Zwroty i reklamacje (default)',
        'identifier' => 'rma-empty-page',
        'content' =>
            <<<EOD
<section id="rma-page">
	<div class="rma-page-header">
		<div class="rma-page-header1">Każdy produkt możesz zwrócić w&nbsp;ciągu 30&nbsp;dni, bez&nbsp;podania&nbsp;przyczyny.</div>
		<div class="rma-page-header2">Produkty zwracasz&nbsp;w <span class="bigger">100%&nbsp;ZA&nbsp;DARMO!</span></div>
	</div>
	<div class="benefits">
		<div class="benefit-item">
			<div class="benefit-text benefit-text-rma" style="background-image: url('/skin/frontend/modago/gallery/images/svg/notebook.svg')">
				Zgłaszasz zwrot lub reklamację w zaledwie kilku klikach.
			</div>
		</div>
		<div class="benefit-item">
			<div class="benefit-text benefit-text-rma" style="background-image: url('/skin/frontend/modago/gallery/images/svg/help_orders.svg')">
				Na bieżąco masz wgląd w status swojego zgłoszenia.
			</div>
		</div>
		<div class="benefit-item">
			<div class="benefit-text benefit-text-rma" style="background-image: url('/skin/frontend/modago/gallery/images/svg/about_delivery_quick.svg')">
				Szybko zamawiasz kuriera przez stronę Modago.pl.
			</div>
		</div>
		<div class="benefit-item">
			<div class="benefit-text benefit-text-rma" style="background-image: url('/skin/frontend/modago/gallery/images/svg/wallet.svg')">
				Błyskawicznie otrzymujesz zwrot płatności.
			</div>
		</div>
	</div>
	<div class="rma-page-footer" style="text-align:left">
		<a href="/zwroty-i-reklamacje">dowiedz się więcej>></a>
	</div>
</section>
EOD
    ,
        'is_active' => 1,
        'stores' => $allStores
    ),
    array(
        'title' => 'Zwroty i reklamacje (default)',
        'identifier' => 'rma-empty-page',
        'content' =>
            <<<EOD
<section id="rma-page">
	<div class="rma-page-header">
		<div class="rma-page-header1">Każdy produkt możesz zwrócić w&nbsp;ciągu 30&nbsp;dni, bez&nbsp;podania&nbsp;przyczyny.</div>
		<div class="rma-page-header2">Produkty zwracasz&nbsp;w <span class="bigger">100%&nbsp;ZA&nbsp;DARMO!</span></div>
	</div>
	<div class="benefits">
		<div class="benefit-item">
			<div class="benefit-text benefit-text-rma" style="background-image: url('/skin/frontend/modago/gallery/images/svg/notebook.svg')">
				Zgłaszasz zwrot lub reklamację w zaledwie kilku klikach.
			</div>
		</div>
		<div class="benefit-item">
			<div class="benefit-text benefit-text-rma" style="background-image: url('/skin/frontend/modago/gallery/images/svg/help_orders.svg')">
				Na bieżąco masz wgląd w status swojego zgłoszenia.
			</div>
		</div>
		<div class="benefit-item">
			<div class="benefit-text benefit-text-rma" style="background-image: url('/skin/frontend/modago/gallery/images/svg/about_delivery_quick.svg')">
				Szybko zamawiasz kuriera przez stronę Modago.pl.
			</div>
		</div>
		<div class="benefit-item">
			<div class="benefit-text benefit-text-rma" style="background-image: url('/skin/frontend/modago/gallery/images/svg/wallet.svg')">
				Błyskawicznie otrzymujesz zwrot płatności.
			</div>
		</div>
	</div>
	<div class="rma-page-footer" style="text-align:left">
		<a href="/zwroty-i-reklamacje">dowiedz się więcej>></a>
	</div>
</section>
EOD
    ,
        'is_active' => 1,
        'stores' => $modagoStore
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