<?php

$massImagesHelp = array(
	array(
		'title'         => 'Zarządzanie zdjęciami - Pomoc',
		'identifier'    => 'vendor-portal-mass-images-help',
		'content'       => <<<EOD
<div class="mass-images-help">
    <p>
        <b>Ładowanie zdjęć</b><br/>
        Zdjęcia można dodawać w następujący sposób:
        <ul>
            <li>poprzez przeciągnięcie myszką obrazków bezpośrednio na okno podglądu
            <li>poprzez otwarcie interfejsu kolejki (Przycisk &quot;Ładowanie zdjęć&quot;) i przeciągnięcie na niego plików 
            <li>poprzez kliknięcie przycisku &quot;Dodaj pliki&quot; na interfejsie kolejki. W tym przypadku obrazy do wgrania wybieramy z listy plików
        </ul>
    </p>

    <p>
        <b>Mapowanie obrazów z produktami</b>        	
        <p>
        Mamy możliwość mapowania w dwojaki sposób, poprzez rozpoznanie nazwy pliku, lub poprzez plik tekstowy. 
        </p>
        <b>Mapowanie po nazwach</b>
        <p>
        Aby plik został rozpoznany musi mieć nazwę w następującym formacie:
        <p>
        <code>
            KOD_PRODUKTU.IDENTYFIKATOR.rozszerzenie np. X2343-AMARANT.3.jpg
        </code> 
        </p>
        IDENTYFIKATOR może być dowolnym ciągiem znaków. Mapowanie po nazwach uruchamia się po naciśnięciu przycisku. Po zakończeniu procesu wyświetlane jest podsumowanie, 
        czyli liczba zmapowanych plików, a pliki dodane do galerii zostają usunięte z podglądu. Jeśli nie zaznaczymy żadnego obrazka, będą mapowane wszystkie, wpp. tylko zaznaczone.
        </p>
        <b>Mapowanie z pliku</b>
        <p>
        Ten rodzaj mapowania wymaga przygotowania pliku tekstowego w formacie CSV.
        Pierwszy wiersz jest nagłówkiem i musi zawierać następujące pola:        
        <p>
        <code>sku;file;label;order</code>
        </p>
        <ul>
            <li>SKU - to kod produktu
            <li>FILE - nazwa pliku z obrazkiem
            <li>LABEL - etykieta obrazka (ciąg znaków)
            <li>ORDER - kolejność dodania obrazków do produktu (obrazki zawsze są dodawane za już istniejącymi, chyba że je nadpisują)
        </ul>
        Kolejne wiersze zawierają wartości rozdzielane średnikami. Kolumny posiadające inne nagłówki są ignorowane.
        </p>
        <p>
        Przy mapowaniu obrazków z pliku istnieje możliwość podpięcia jednego obrazka pod wiele produktów. Po zakończeniu procesu wyświetlana jest ilość zmapowanych produktów, a obrazki są usuwane z podglądu.
        </p>
        <p>
        Przykładowy plik:
        </p>
        <p>
            <pre>
                sku;file;format;label;order
                6936-AMARANT;picture1.jpg;JPG;Widok front;1
                6936-AMARANT;picture2.jpg;JPG;Widok tył;2
                123124;picture_test.jpg;JPG;Obrazek;1
            </pre>
        </p>
        <p>
        W powyższym przykładzie kolumna <code>format</code> jest ignorowana
    </p>        
</div>
EOD
	,
		'is_active'     => 1,
		'stores'        => 0
	)
);

foreach ($massImagesHelp as $data) {
	$block = Mage::getModel('cms/block')->load($data['identifier']);
	if ($block->getBlockId()) {
		$oldData = $block->getData();
		$data = array_merge($oldData,$data);
	}
	$block->setData($data)->save();
}