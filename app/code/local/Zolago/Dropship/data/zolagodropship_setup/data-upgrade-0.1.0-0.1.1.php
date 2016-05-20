<?php
$blocks = array();
$blocks[] = array (
	"title" => "Panel vendora - Pomoc - Domyślny blok",
	"identifier" => "udropship-help-pl",
  "content" => 
<<<EOT
<h4>JAK KORZYSTAĆ Z PANELU POMOCY</h4>
<p>Na każdej podstronie panelu administracyjnego, po kliknięciu w przycisk „Pomoc” wysunie się panel (taki jak ten) z kontekstową pomocą. Jeśli więc masz jakieś wątpliwość związane z zawartością strony, nie wiesz w jaki sposób wprowadzić zmianę czy nie rozumiesz jakiegoś oznaczenia, otwórz panel pomocy i poszukaj odpowiedzi na swoje pytania. Staraliśmy się opisać dokładnie poszczególne funkcje i procesy, jeśli jednak po zapoznaniu się z treścią pomocy, nadal masz jakieś wątpliwości, skontaktuj się z nami, a chętnie wszystko wyjaśnimy. </p>
EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "Panel vendora - Pomoc - Reklama i promocja ",
	"identifier" => "udropship-help-pl-campaign",
  "content" => 
<<<EOT
<h4>KAMPANIE</h4>

<p><h5>DODAWANIE KAMPANII</h5></p>
<p>Kliknij w przycisk „Dodaj nową kampanię”, aby przejść na stronę definiowania nowej kampanii. Pola zaznaczone gwiazdką są obowiązkowe. Po dodaniu nowej kampanii można dodać do niej kreacje i produkty.</p>
<p>Poniżej objaśnienie poszczególnych pól:
<p><b>Twoja nazwa kampanii </b>– nazwa kampanii, które będzie widoczna w panelu administracyjnym, jest to nazwa do użytku wewnętrznego i nie będzie prezentowana publicznie. </p>

<p><b>Publiczna nazwa kampanii</b> – jest to nazwa oficjalna kampanii, która może być wykorzystana do komunikacji z Klientami i w działaniach promocyjnych (np. pokazana na stronie docelowej kampanii). </p>

<p><b>Typ kampanii </b>– wybierz typ kampanii, który najlepiej ją określa. Systemowo zdefiniowane są 3 typy kampanii: 

<ul style="padding-left=20px">
<p><li><b>Wyprzedaż</b> – duża obniżka cen, kolekcje do wyprzedania, produkty dodane do kampanii wyprzedażowej otrzymają automatycznie plakietkę wyprzedaży i będą oznaczone na stronie serwisu jako wyprzedażowe.</li></p>

<p><li><b>Promocja – produkty w obniżonych cenach, produkty dodane do kampanii promocyjnej otrzymają automatycznie plakietkę promocji i będą oznaczone na stronie serwisu jako produkty w promocji.</li></p>


<p><li><b>Informacyjna – kampania tematyczna np. najmodniejsze w tym sezonie suknie ślubne czy nowa kolekcja, nie związana z obniżką cenową, produkty dodane do takiej kampanii nie zostaną jakoś specjalnie oznaczone.</li></p>


Status kampanii - wybierz jeden ze statusów:
Aktywna – aby kampania była widoczna na stronie serwisu musi mieć status aktywny oraz właściwą datę obowiązywania.
Nieaktywna – jeśli kampania ma status nieaktywny, nie będzie widoczna na stronie serwisu. . Status ten można stosować w celu szybkiego wstrzymania kampanii. 
Archiwalna - jeśli kampania ma status archiwalny, nie będzie widoczna na stronie serwisu; status archiwalny jest automatycznie ustawiany dla kampanii, które już się zakończyły (w momencie przekroczenia terminu obowiązywania kampanii). Status ten pozwala oznaczyć kampanie historyczne, aby zachować porządek w systemie, bez potrzeby usuwania nieaktualnych kampanii. 

URL – należy wkleić skopiowany z paska przeglądarki końcowy fragment adresu URL strony, do której chcemy aby prowadziła kampania. Adres strony powinien być względny, tzn. określać część po nazwie domeny, np. jeśli pełny adres docelowy dla kampanii ma być modago.pl/giorginio/bluzki to należy tu wprowadzić adres /giorginio/bluzki.
Data obowiązywania – tworząc kampanię należy określić kiedy powinna się zacząć i do kiedy trwać. Jeśli kampania ma status „Aktywna”, system automatycznie uruchomi i wyłączy kampanię w zdefiniowanych terminach. 

Witryny – możesz wybrać, na których serwisach, w których prezentujesz swoją ofertę chcesz wyświetlać kampanię. 

Źródło ceny specjalnej – jeśli produkty w kampanii mają automatycznie otrzymać niższą cenę (szczególnie w przypadku promocji lub wyprzedaży), trzeba określić z którego cennika  ma być pobrana specjalna, promocyjna cena. Najczęściej użyjesz tu podstawowej ceny A. Jeśli nie jesteś pewien jakie cenniki masz do dyspozycji skonsultuj się z osobą, która uczestniczyła w integracji Twojej oferty produktowej lub z zespołem wsparcia Modago.pl.
 
Rabat – jeśli produkty w kampanii mają automatycznie otrzymać rabat, należy w polu wpisać procentową wartość rabatu np. jeśli chcemy aby każdy produkt w danej kampanii był prezentowany z trzydziestoprocentowym rabatem, należy wpisać w pole wartość 30.  Jeśli nie wypełnisz tego pola, nie będzie naliczony jakikolwiek rabat. 

Cena przekreślona - jeśli w produktach w kampanii mają być pokazane przekreślone ceny (szczególnie w przypadku promocji lub wyprzedaży), trzeba wskazać skąd ma być pobrana przekreślona cena.
Poprzednia cena – będzie prezentowana cena sprzed obniżki. 
Cena przekreślona w produkcie – będzie prezentowana cena zdefiniowana w produkcie. Cena ta może być wprowadzona ręcznie lub może pochodzić z pliku, który dostarczasz do Modago.pl.

DODAWANIE KREACJI DO KAMPANII
Kliknij w przycisk „Dodaj nową kreację”, aby przejść na stronę definiowania nowej kreacji. Musisz zacząć od wyboru typu kreacji. W zależności od typu kreacji otrzymasz inne pola do wypełnienia. Dla każdego typu kreacji należy określić nazwę, która będzie ją identyfikowała w panelu administracyjnym. 

Slider – jest to forma banneru – dużych obrazków, wyświetlanych na samej górze strony. Na stronie mogą być maksymalnie 3 bannery na raz, które automatycznie rotują lub mogą być przewijane ręcznie. Ponieważ strona jest responsywna, banner będzie wyświetlany w różnej wielkości. Elementy, które należy wypełnić:
Obrazek podstawowy – to jest obrazek, który będzie prezentowany w pełnych rozdzielczościach ekranu. Aby zdjęcie wyglądało właściwie – było ostre i wyraźne – należy wgrać plik o formacie JPG lub PNG w wymiarach - szerokość: 1174px, wysokość: 400px. UWAGA! Zdjęcia o mniejszej szerokości nie będą właściwie wyświetlać się na stronie. Aby dodać zdjęcie, trzeba kliknąć „Przeglądaj” i wskazać plik, który ma zostać wgrany z komputera. 
Obrazek mobilny – to jest obrazek, który będzie prezentowany na małych ekranach – ma bardziej optymalne proporcje dla urządzeń mobilnych i pozwala dopasować zawartość do mniejszego rozmiaru kreacji. Aby zdjęcie wyglądało właściwie – było ostre i wyraźne – należy wgrać plik o formacie JPG lub PNG w wymiarach - szerokość: 750px, wysokość: 400px. UWAGA! Zdjęcia o mniejszej szerokości nie będą właściwie wyświetlać się na stronie.  Aby dodać zdjęcie, trzeba kliknąć „Przeglądaj” i wskazać plik, który ma zostać wgrany z komputera.
Adres URL- należy skopiować z okna przeglądarki URL strony, do której ma prowadzić kreacja. Jeśli nie wypełnisz pola, zostanie automatycznie zaciągnięty adres URL zdefiniowany dla kampanii. 

Boks – są to mniejsze formy reklamowe – mniejsze obrazy, które znajdują się na stronie zazwyczaj pod głównymi bannerami. Na stronie może być maksymalnie 6 boksów. 
Obrazek – w przypadku boksów, ten sam obrazek prezentowany jest na wszystkich ekranach. Aby zdjęcie wyglądało właściwie – było ostre i wyraźne – należy wgrać plik o formacie JPG lub PNG w wymiarach - szerokość: 366px, wysokość: 422px. UWAGA! Zdjęcia o mniejszej szerokości nie będą właściwie wyświetlać się na stronie.  Aby dodać zdjęcie, trzeba kliknąć „Przeglądaj” i wskazać plik, który ma zostać wgrany z komputera.
Adres URL- należy skopiować z okna przeglądarki URL strony, do której ma prowadzić kreacja. Jeśli nie wypełnisz pola, zostanie automatycznie zaciągnięty adres URL zdefiniowany dla kampanii. 

Inspiracje – są to formy bardziej informacyjne, niż reklamowe, składające się z prostego obrazka i tekstu. Są opcjonalne i wyświetlane są zazwyczaj pod boksami reklamowymi. 
Obrazek – w przypadku inspiracji, ten sam obrazek prezentowany jest na wszystkich ekranach. Aby zdjęcie wyglądało właściwie – było ostre i wyraźne – należy wgrać plik o formacie JPG lub PNG w wymiarach - szerokość: 330px, wysokość: 494px. UWAGA! Zdjęcia wgrane w innym rozmiarze nie będą właściwie wyświetlać się na stronie.  Aby dodać zdjęcie, trzeba kliknąć „Przeglądaj” i wskazać plik, który ma zostać wgrany z komputera.
Adres URL- należy skopiować z okna przeglądarki URL strony, do której ma prowadzić kreacja. Jeśli nie wypełnisz pola, zostanie automatycznie zaciągnięty adres URL zdefiniowany dla kampanii. 


DODAWANIE PRODUKTÓW DO KAMPANII
Kliknij w przycisk „Dodaj produkty”, aby otworzyć okienko dodawania produktów. Wprowadź własne kody produktów (SKU), rozdzielone przecinkami (ze spacją lub bez) i kliknij „Dodaj”. Aby dodać kolejne produkty, trzeba jeszcze raz kliknąć w „Dodaj produkty” i dopisać kolejne do wyświetlonej listy. Produkty możesz usuwać z kampanii klikając w krzyżyk przy produkcie, na liście produktów lub usuwając z listy, która pojawia się po kliknięciu w „Dodaj produkty”. 

EDYTOWANIE KAMPANII
Każdą kampanię można edytować. Na stronie edytowania można zmienić ustawienia kampanii, dodać do niej kreacje i produkty. Każdą zmianę należy zapisać.  

EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "Panel vendora - Pomoc - Reklama i promocja - Kampanie - Powierzchnia reklamowa",
	"identifier" => "udropship-help-pl-campaign-placement",
  "content" => 
<<<EOT
<h4> POWIERZCHNIA REKLAMOWA </h4>
<p>Na stronie „Zarządzanie powierzchnią reklamową” zobaczysz listę wszystkich stron serwisu, na których możesz zamieszczać reklamy graficzne - stronę główną i strony kategorii produktowych w Twoim sklepie. Wybierz stronę, której zawartość reklamową chcesz edytować klikając w „Edytuj” przy nazwie strony.</p>

<br/>
<h5>DODAWANIE KREACJI NA STRONĘ</h5>
<p>Po wybraniu strony, przejdziesz do szczegółów dostępnej na niej powierzchni reklamowej. Dostępne są 3 miejsca tzw. sloty na bannery, 6 slotów na boksy i 8 slotów na inspiracje. Oznacza to, że jednocześnie na stronie mogą pokazywać się maksymalnie 3 bannery, 6 boksów i 8 inspiracji. Aktualnie inspiracje publikowane są tylko na stronie głównej Modago.<p>

<p>
<b>Dodawanie kreacji do slota</b><br/>
Aby wrzucić nową kreację na stronę, kliknij przycisk „Dodaj” przy wybranym slocie. Otworzy się okienko dodawania kreacji. Wybierz z listy najpierw kampanię, w której jest kreacja (będą widoczne jedynie aktywne kampanie), a potem kreację z wybranej kampanii. Zobaczysz z prawej strony podgląd kreacji. Wybór musisz potwierdzić klikając w „Zapisz”. </p>

<p>
<b>Zmiana priorytetu kreacji</b><br/>
Do każdego ze slotów możesz dodać po kilka kreacji określając dla nich priorytet w ramach slotu, gdzie najważniejsza kreacja ma priorytet 1. W danym momencie wyświetlana jest tylko jedna kreacja w slocie ale ponieważ kampanie mają różny czas trwania, dzięki priorytetom, możesz stworzyć taką kombinację kreacji, która zapewni ciągłość reklamową i stałe wyświetlanie się reklam na różnych slotach. Po zakończeniu się kampanii o najwyższym priorytecie zacznie być wyświetlana następna kreacja z kolei. Aby zmienić priorytet wystarczy przeciągnąć kreację na inną pozycję w ramach slotu lub kliknąć w ikonkę ołówka (edycji) na wybranej w slocie kreacji i zmienić priorytet wybierając inną liczbę z listy rozwijanej. Wszystkie zmiany są automatycznie zapisywane..</p>

<p>
<b>Podgląd kreacji w slocie reklamowym</b><br/>
W każdym ze slotów widać aktualnie dodane kreacje – podgląd obrazka, nazwę kampanii, nazwę kreacji, oraz czas trwania kampanii wraz z ikonką, która sygnalizuje czas do zakończenia kampanii. </p>

<p><i data-edit-val="link-edit-status" class="icon-ok"></i> - oznacza, że kampania jest aktywna i kreacja prezentowana jest na stronie</p>
<p><i data-edit-val="link-edit-status" class="icon-warning-sign"></i>  - oznacza, że niedługo kończy się czas trwania kampanii i kreacja przestanie się wyświetlać. Z poziomu edycji kreacji możesz przejść do strony kampanii i zmodyfikować jej ustawienia. </p>
<p><i data-edit-val="link-edit-status" class="icon-remove"></i> - oznacza, że kampania już się zakończyła lub jest nieaktywna i kreacja nie jest już prezentowana na stronie.</p>

<p>
<b>Usuwanie kreacji </b><br/>
Jeśli chcesz usunąć kreację, najpierw kliknij w ikonkę ołówka (edycji) na podglądzie kreacji, a gdy pojawi się okienko edycji kreacji, kliknij w link „usuń tę kreację z tego miejsca” i potwierdź operację.  </p><br/><br/>

EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "Panel vendora - Pomoc - Reklama i promocja - Kampanie - Powierzchnia reklamowa - Strona",
	"identifier" => "udropship-help-pl-campaign-placement-category",
  "content" => 
<<<EOT
<h4>DODAWANIE KREACJI NA STRONĘ</h4>
<p>Dla każdej strony dostępne są 3 miejsca tzw. sloty na bannery, 6 slotów na boksy i 8 slotów na inspiracje. Oznacza to, że jednocześnie na stronie mogą pokazywać się maksymalnie 3 bannery, 6 boksów i 8 inspiracji. Aktualnie inspiracje publikowane są tylko na stronie głównej Modago.<p>

<p>
<b>Dodawanie kreacji do slota</b><br/>
Aby wrzucić nową kreację na stronę, kliknij przycisk „Dodaj” przy wybranym slocie. Otworzy się okienko dodawania kreacji. Wybierz z listy najpierw kampanię, w której jest kreacja (będą widoczne jedynie aktywne kampanie), a potem kreację z wybranej kampanii. Zobaczysz z prawej strony podgląd kreacji. Wybór musisz potwierdzić klikając w „Zapisz”. </p>

<p>
<b>Zmiana priorytetu kreacji</b><br/>
Do każdego ze slotów możesz dodać po kilka kreacji określając dla nich priorytet w ramach slotu, gdzie najważniejsza kreacja ma priorytet 1. W danym momencie wyświetlana jest tylko jedna kreacja w slocie ale ponieważ kampanie mają różny czas trwania, dzięki priorytetom, możesz stworzyć taką kombinację kreacji, która zapewni ciągłość reklamową i stałe wyświetlanie się reklam na różnych slotach. Po zakończeniu się kampanii o najwyższym priorytecie zacznie być wyświetlana następna kreacja z kolei. Aby zmienić priorytet wystarczy przeciągnąć kreację na inną pozycję w ramach slotu lub kliknąć w ikonkę ołówka (edycji) na wybranej w slocie kreacji i zmienić priorytet wybierając inną liczbę z listy rozwijanej. Wszystkie zmiany są automatycznie zapisywane..</p>


<p>
<b>Podgląd kreacji w slocie reklamowym</b><br/>
W każdym ze slotów widać aktualnie dodane kreacje – podgląd obrazka, nazwę kampanii, nazwę kreacji, oraz czas trwania kampanii wraz z ikonką, która sygnalizuje czas do zakończenia kampanii. </p>

<p><i data-edit-val="link-edit-status" class="icon-ok"></i> - oznacza, że kampania jest aktywna i kreacja prezentowana jest na stronie</p>
<p><i data-edit-val="link-edit-status" class="icon-warning-sign"></i>  - oznacza, że niedługo kończy się czas trwania kampanii i kreacja przestanie się wyświetlać. Z poziomu edycji kreacji możesz przejść do strony kampanii i zmodyfikować jej ustawienia. </p>
<p><i data-edit-val="link-edit-status" class="icon-remove"></i> - oznacza, że kampania już się zakończyła lub jest nieaktywna i kreacja nie jest już prezentowana na stronie.</p>

<p>
<b>Usuwanie kreacji </b><br/>
Jeśli chcesz usunąć kreację, najpierw kliknij w ikonkę ołówka (edycji) na podglądzie kreacji, a gdy pojawi się okienko edycji kreacji, kliknij w link „usuń tę kreację z tego miejsca” i potwierdź operację.  </p><br/><br/>

EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "Panel vendora - Pomoc - Reklama i promocja - Kampanie",
	"identifier" => "udropship-help-pl-campaign-vendor",
  "content" => 
<<<EOT
<h4>KAMPANIE</h4>

<p><h5>DODAWANIE KAMPANII</h5></p>
<p>Kliknij w przycisk „Dodaj nową kampanię”, aby przejść na stronę definiowania nowej kampanii. Pola zaznaczone gwiazdką są obowiązkowe. Po dodaniu nowej kampanii można dodać do niej kreacje i produkty.</p>
<p>Poniżej objaśnienie poszczególnych pól:

<p><b>Twoja nazwa kampanii </b>– nazwa kampanii, które będzie widoczna w panelu administracyjnym, jest to nazwa do użytku wewnętrznego i nie będzie prezentowana publicznie. </p>

<p><b>Publiczna nazwa kampanii</b> – jest to nazwa oficjalna kampanii, która może być wykorzystana do komunikacji z Klientami i w działaniach promocyjnych (np. pokazana na stronie docelowej kampanii). </p>

<p><b>Typ kampanii </b>– wybierz typ kampanii, który najlepiej ją określa. Systemowo zdefiniowane są 3 typy kampanii: 

<ul style="padding-left:20px">
<p><li>Wyprzedaż – duża obniżka cen, kolekcje do wyprzedania, produkty dodane do kampanii wyprzedażowej otrzymają automatycznie plakietkę wyprzedaży i będą oznaczone na stronie serwisu jako wyprzedażowe.</li></p>

<p><li>Promocja – produkty w obniżonych cenach, produkty dodane do kampanii promocyjnej otrzymają automatycznie plakietkę promocji i będą oznaczone na stronie serwisu jako produkty w promocji.</li></p>


<p><li>Informacyjna – kampania tematyczna np. najmodniejsze w tym sezonie suknie ślubne czy nowa kolekcja, nie związana z obniżką cenową, produkty dodane do takiej kampanii nie zostaną jakoś specjalnie oznaczone.</li></p>
</ul>

<p><b>Status kampanii </b>- wybierz jeden ze statusów:
<ul style="padding-left:20px">
<p><li>Aktywna – aby kampania była widoczna na stronie serwisu musi mieć status aktywny oraz właściwą datę obowiązywania.</li></p>
<p><li>Nieaktywna – jeśli kampania ma status nieaktywny, nie będzie widoczna na stronie serwisu. . Status ten można stosować w celu szybkiego wstrzymania kampanii. </li></p>
<p><li>Archiwalna - jeśli kampania ma status archiwalny, nie będzie widoczna na stronie serwisu; status archiwalny jest automatycznie ustawiany dla kampanii, które już się zakończyły (w momencie przekroczenia terminu obowiązywania kampanii). Status ten pozwala oznaczyć kampanie historyczne, aby zachować porządek w systemie, bez potrzeby usuwania nieaktualnych kampanii. </li></p>
</ul>

<p><b>URL </b>– należy wkleić skopiowany z paska przeglądarki końcowy fragment adresu URL strony, do której chcemy aby prowadziła kampania. Adres strony powinien być względny, tzn. określać część po nazwie domeny, np. jeśli pełny adres docelowy dla kampanii ma być <i>modago.pl/giorginio/bluzki </i>to należy tu wprowadzić adres<i> /giorginio/bluzki</i>.</p>

<p><b>Data obowiązywania </b>– tworząc kampanię należy określić kiedy powinna się zacząć i do kiedy trwać. Jeśli kampania ma status „Aktywna”, system automatycznie uruchomi i wyłączy kampanię w zdefiniowanych terminach. </p>

<p><b>Witryny  </b>– możesz wybrać, na których serwisach, w których prezentujesz swoją ofertę chcesz wyświetlać kampanię. </p>

<p><b>Źródło ceny specjalnej </b> – jeśli produkty w kampanii mają automatycznie otrzymać niższą cenę (szczególnie w przypadku promocji lub wyprzedaży), trzeba określić z którego cennika  ma być pobrana specjalna, promocyjna cena. Najczęściej użyjesz tu podstawowej ceny A. Jeśli nie jesteś pewien jakie cenniki masz do dyspozycji skonsultuj się z osobą, która uczestniczyła w integracji Twojej oferty produktowej lub z zespołem wsparcia Modago.pl.</p>
 
<p><b>Rabat </b> – jeśli produkty w kampanii mają automatycznie otrzymać rabat, należy w polu wpisać procentową wartość rabatu np. jeśli chcemy aby każdy produkt w danej kampanii był prezentowany z trzydziestoprocentowym rabatem, należy wpisać w pole wartość 30.  Jeśli nie wypełnisz tego pola, nie będzie naliczony jakikolwiek rabat. </p>

<p><b>Cena przekreślona  </b>- jeśli w produktach w kampanii mają być pokazane przekreślone ceny (szczególnie w przypadku promocji lub wyprzedaży), trzeba wskazać skąd ma być pobrana przekreślona cena.<br/>
Poprzednia cena – będzie prezentowana cena sprzed obniżki. <br/>
Cena przekreślona w produkcie – będzie prezentowana cena zdefiniowana w produkcie. Cena ta może być wprowadzona ręcznie lub może pochodzić z pliku, który dostarczasz do Modago.pl.<p>

<br/>
<h5>DODAWANIE KREACJI DO KAMPANII</h5>
<p>Kliknij w przycisk „Dodaj nową kreację”, aby przejść na stronę definiowania nowej kreacji. Musisz zacząć od wyboru typu kreacji. W zależności od typu kreacji otrzymasz inne pola do wypełnienia. Dla każdego typu kreacji należy określić nazwę, która będzie ją identyfikowała w panelu administracyjnym. </p>

<p><b>Slider </b>– jest to forma banneru – dużych obrazków, wyświetlanych na samej górze strony. Na stronie mogą być maksymalnie 3 bannery na raz, które automatycznie rotują lub mogą być przewijane ręcznie. Ponieważ strona jest responsywna, banner będzie wyświetlany w różnej wielkości. Elementy, które należy wypełnić:
<ul style="padding-left:20px">
<p><li>Obrazek podstawowy – to jest obrazek, który będzie prezentowany w pełnych rozdzielczościach ekranu. Aby zdjęcie wyglądało właściwie – było ostre i wyraźne – należy wgrać plik o formacie JPG lub PNG w wymiarach - szerokość: 1174px, wysokość: 400px. UWAGA! Zdjęcia o mniejszej szerokości nie będą właściwie wyświetlać się na stronie. Aby dodać zdjęcie, trzeba kliknąć „Przeglądaj” i wskazać plik, który ma zostać wgrany z komputera. </li></p>
<p><li>Obrazek mobilny – to jest obrazek, który będzie prezentowany na małych ekranach – ma bardziej optymalne proporcje dla urządzeń mobilnych i pozwala dopasować zawartość do mniejszego rozmiaru kreacji. Aby zdjęcie wyglądało właściwie – było ostre i wyraźne – należy wgrać plik o formacie JPG lub PNG w wymiarach - szerokość: 750px, wysokość: 400px. UWAGA! Zdjęcia o mniejszej szerokości nie będą właściwie wyświetlać się na stronie.  Aby dodać zdjęcie, trzeba kliknąć „Przeglądaj” i wskazać plik, który ma zostać wgrany z komputera.</li></p>
<p><li>Adres URL - należy skopiować z okna przeglądarki URL strony, do której ma prowadzić kreacja. Jeśli nie wypełnisz pola, zostanie automatycznie zaciągnięty adres URL zdefiniowany dla kampanii. </li></p>
</ul>

<p><b>Boks</b> – są to mniejsze formy reklamowe – mniejsze obrazy, które znajdują się na stronie zazwyczaj pod głównymi bannerami. Na stronie może być maksymalnie 6 boksów. 
<ul style="padding-left:20px">
<p><li>Obrazek – w przypadku boksów, ten sam obrazek prezentowany jest na wszystkich ekranach. Aby zdjęcie wyglądało właściwie – było ostre i wyraźne – należy wgrać plik o formacie JPG lub PNG w wymiarach - szerokość: 366px, wysokość: 422px. UWAGA! Zdjęcia o mniejszej szerokości nie będą właściwie wyświetlać się na stronie.  Aby dodać zdjęcie, trzeba kliknąć „Przeglądaj” i wskazać plik, który ma zostać wgrany z komputera.</li></p>

<p><li>Adres URL- należy skopiować z okna przeglądarki URL strony, do której ma prowadzić kreacja. Jeśli nie wypełnisz pola, zostanie automatycznie zaciągnięty adres URL zdefiniowany dla kampanii. </li></p>
</ul>

<p><b>Inspiracje </b>– są to formy bardziej informacyjne, niż reklamowe, składające się z prostego obrazka i tekstu. Są opcjonalne i wyświetlane są zazwyczaj pod boksami reklamowymi. 
<ul style="padding-left:20px">
<p><li>Obrazek – w przypadku inspiracji, ten sam obrazek prezentowany jest na wszystkich ekranach. Aby zdjęcie wyglądało właściwie – było ostre i wyraźne – należy wgrać plik o formacie JPG lub PNG w wymiarach - szerokość: 330px, wysokość: 494px. UWAGA! Zdjęcia wgrane w innym rozmiarze nie będą właściwie wyświetlać się na stronie.  Aby dodać zdjęcie, trzeba kliknąć „Przeglądaj” i wskazać plik, który ma zostać wgrany z komputera.</li></p>
<p><li>Adres URL - należy skopiować z okna przeglądarki URL strony, do której ma prowadzić kreacja. Jeśli nie wypełnisz pola, zostanie automatycznie zaciągnięty adres URL zdefiniowany dla kampanii. </li></p>
</ul>
<br/>
<h5>DODAWANIE PRODUKTÓW DO KAMPANII</h5>
<p>Kliknij w przycisk „Dodaj produkty”, aby otworzyć okienko dodawania produktów. <br/>
Wprowadź własne kody produktów (SKU), rozdzielone przecinkami (ze spacją lub bez) i kliknij „Dodaj”.<br/>
Aby dodać kolejne produkty, trzeba jeszcze raz kliknąć w „Dodaj produkty” i dopisać kolejne do wyświetlonej listy. <br/>
Produkty możesz usuwać z kampanii klikając w krzyżyk przy produkcie, na liście produktów lub usuwając z listy, która pojawia się po kliknięciu w „Dodaj produkty”. </p>

<br/>
<h5>EDYTOWANIE KAMPANII</h5>
<p>Każdą kampanię można edytować. Na stronie edytowania można zmienić ustawienia kampanii, dodać do niej kreacje i produkty. Każdą zmianę należy zapisać.  </p>
<br/><br/>

EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "Panel vendora - Pomoc - Reklama i promocja - Kampanie - Dodawanie kampanii",
	"identifier" => "udropship-help-pl-campaign-vendor-edit",
  "content" => 
<<<EOT
<h4>DODAWANIE KAMPANII</h4>
<p>Pola zaznaczone gwiazdką są obowiązkowe. Poniżej objaśnienie poszczególnych pól:

<p><b>Twoja nazwa kampanii </b>– nazwa kampanii, które będzie widoczna w panelu administracyjnym, jest to nazwa do użytku wewnętrznego i nie będzie prezentowana publicznie. </p>

<p><b>Publiczna nazwa kampanii</b> – jest to nazwa oficjalna kampanii, która może być wykorzystana do komunikacji z Klientami i w działaniach promocyjnych (np. pokazana na stronie docelowej kampanii). </p>

<p><b>Typ kampanii </b>– wybierz typ kampanii, który najlepiej ją określa. Systemowo zdefiniowane są 3 typy kampanii: 

<ul style="padding-left:20px">
<p><li>Wyprzedaż – duża obniżka cen, kolekcje do wyprzedania, produkty dodane do kampanii wyprzedażowej otrzymają automatycznie plakietkę wyprzedaży i będą oznaczone na stronie serwisu jako wyprzedażowe.</li></p>

<p><li>Promocja – produkty w obniżonych cenach, produkty dodane do kampanii promocyjnej otrzymają automatycznie plakietkę promocji i będą oznaczone na stronie serwisu jako produkty w promocji.</li></p>


<p><li>Informacyjna – kampania tematyczna np. najmodniejsze w tym sezonie suknie ślubne czy nowa kolekcja, nie związana z obniżką cenową, produkty dodane do takiej kampanii nie zostaną jakoś specjalnie oznaczone.</li></p>
</ul>

<p><b>Status kampanii </b>- wybierz jeden ze statusów:
<ul style="padding-left:20px">
<p><li>Aktywna – aby kampania była widoczna na stronie serwisu musi mieć status aktywny oraz właściwą datę obowiązywania.</li></p>
<p><li>Nieaktywna – jeśli kampania ma status nieaktywny, nie będzie widoczna na stronie serwisu. . Status ten można stosować w celu szybkiego wstrzymania kampanii. </li></p>
<p><li>Archiwalna - jeśli kampania ma status archiwalny, nie będzie widoczna na stronie serwisu; status archiwalny jest automatycznie ustawiany dla kampanii, które już się zakończyły (w momencie przekroczenia terminu obowiązywania kampanii). Status ten pozwala oznaczyć kampanie historyczne, aby zachować porządek w systemie, bez potrzeby usuwania nieaktualnych kampanii. </li></p>
</ul>

<p><b>URL </b>– należy wkleić skopiowany z paska przeglądarki końcowy fragment adresu URL strony, do której chcemy aby prowadziła kampania. Adres strony powinien być względny, tzn. określać część po nazwie domeny, np. jeśli pełny adres docelowy dla kampanii ma być <i>modago.pl/giorginio/bluzki </i>to należy tu wprowadzić adres<i> /giorginio/bluzki</i>.</p>

<p><b>Data obowiązywania </b>– tworząc kampanię należy określić kiedy powinna się zacząć i do kiedy trwać. Jeśli kampania ma status „Aktywna”, system automatycznie uruchomi i wyłączy kampanię w zdefiniowanych terminach. Pamiętaj aby stałe kampanie ustawić na bardzo odległy termin, aby się niechcący nie wyłączyły zastawiając puste pola reklamowe. </p>

<p><b>Witryny  </b>– możesz wybrać, na których serwisach, w których prezentujesz swoją ofertę chcesz wyświetlać kampanię. </p>

<p><b>Źródło ceny specjalnej </b> – jeśli produkty w kampanii mają automatycznie otrzymać niższą cenę (szczególnie w przypadku promocji lub wyprzedaży), trzeba określić z którego cennika  ma być pobrana specjalna, promocyjna cena. Najczęściej użyjesz tu podstawowej ceny A. Jeśli nie jesteś pewien jakie cenniki masz do dyspozycji skonsultuj się z osobą, która uczestniczyła w integracji Twojej oferty produktowej lub z zespołem wsparcia Modago.pl.</p>
 
<p><b>Rabat </b> – jeśli produkty w kampanii mają automatycznie otrzymać rabat, należy w polu wpisać procentową wartość rabatu np. jeśli chcemy aby każdy produkt w danej kampanii był prezentowany z trzydziestoprocentowym rabatem, należy wpisać w pole wartość 30. Pole jest obowiązkowe, jeśli więc nie chcesz dawać rabatu, wpisz liczbę zero. </p>

<p><b>Cena przekreślona  </b>- jeśli w produktach w kampanii mają być pokazane przekreślone ceny (szczególnie w przypadku promocji lub wyprzedaży), trzeba wskazać skąd ma być pobrana przekreślona cena.<br/>
Poprzednia cena – będzie prezentowana cena sprzed obniżki. <br/>
Cena przekreślona w produkcie – będzie prezentowana cena zdefiniowana w produkcie. Cena ta może być wprowadzona ręcznie lub może pochodzić z pliku, który dostarczasz do Modago.pl.<p>

<br/>
EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "udropship-help-pl-udpo",
	"identifier" => "udropship-help-pl-udpo",
  "content" => 
<<<EOT
udropship-help-pl-udpo
EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "Panel vendora - Pomoc - Zamówienia - Lista zamówień",
	"identifier" => "udropship-help-pl-udpo-vendor",
  "content" => 
<<<EOT
<h4>ZAMÓWIENIA</h4>

<p>Na liście zamówień zobaczysz wszystkie swoje zamówienia wraz z podglądem najważniejszych danych zamówienia. Klikając w „Edytuj” przy wybranym zamówieniu wejdziesz w kartę zamówienia i wszystkie informacje z nim związane. </p>

<br/>
<h5>FILTROWANIE I SORTOWANIE LISTY ZAMÓWIEŃ</h5>
<p>W sekcji „Filtry” możesz je filtrować po najważniejszych polach – statusie, dacie zamówienia, dacie wysyłki czy punkcie sprzedaży. Możesz też skorzystać z pól filtrowania dla poszczególnych kolumn (pola pod nagłówkami kolumn). Zatwierdź swój wybór klikając w ENTER lub przycisk „Filtruj”. Aby sortować zamówienia, kliknij w wybraną kolumnę – strzałeczka przy nazwie kolumny wskaże Ci porządek sortowania (malejący lub rosnący). </p>

<br/>
<h5>STATUSY ZAMÓWIEŃ</h5>
<p>Na karcie zamówienia, w prawym górnym rogu znajduje się zazwyczaj podpowiedź dotycząca następnego kroku realizacji zamówienia i przycisk zmiany statusu na kolejny. Dodatkowe opcje zmiany statusu (w tym „Problem” i „Anulowane”) są dostępne po kliknięciu w pole aktualnego (w informacjach ogólnych). Niektóre zmiany statusów (zacznij pakowanie, wystaw zbiorówkę, potwierdź rezerwację, skieruj do realizacji) możesz zmienić grupowo z poziomu listy zamówień, jeśli aktualny status tych zamówień na to pozwala. Poniżej objaśnienie statusów:
<ul style="padding-left:20px">

<p><li><b>Oczekuje na potwierdzenie</b>– Nowe zamówienia mogą być zarejestrowane w systemie w statusie „Oczekuje na potwierdzenie”. Dzieje się tak, gdy z zamówieniem związany jest jakiś alert. Alert wyświetlany jest w lewym górnym rogu na czerwono. Alerty są tworzone automatycznie przez system, w sytuacji gdy z jakiegoś powodu zamówienie wyda się „podejrzane” i wymaga decyzji lub wykonania jakiejś czynności lub sprawdzenia przez operatora. Zanim zaakceptujesz zamówienie klikając w przycisk „Akceptuj zamówienie” w prawym górnym rogu strony zamówienia – koniecznie sprawdź czy można to zrobić. </li></p>

<p><li><b>Oczekuje na rezerwację </b>– Status dotyczy zamówień z przedpłatą, informuje o potrzebie rezerwacji towaru pod zamówienie, na czas oczekiwania na płatność. Odłóż zamówione przez Klienta produkty do zarezerwowanych, tak żeby były na pewno dostępne i gotowe do wysyłki. Zamówienie nie może być realizowane dopóki nie potwierdzisz dokonania rezerwacji klikając w przycisk „Potwierdź rezerwację produktów” (w prawym górnym rogu strony zamówienia). </li></p>

<p><li><b>Oczekuje na zapłatę</b> – Zamówienie nie jest jeszcze opłacone, nie może więc zostać wysłane, status dotyczy zarezerwowanych, z opcją przedpłaty.</li></p>

<p><li><b>Oczekuje na spakowanie </b>– Zamówienie może zostać wysłane, czeka na spakowanie. Status dotyczy zamówień, które są już opłacone albo za pobraniem. Zamówienia w tym statusie powinny być skompletowane i wysłane jak najszybciej. Kliknij przycisk „Zacznij pakować” (w prawym górnym rogu strony zamówienia), aby zmienić status.</li></p>

<p><li><b>W trakcie pakowania </b>– Ustaw ten status w momencie przystąpienia do pakowania zamówienia. Następnym krokiem będzie wygenerowanie listu przewozowego klikając w przycisk „Wydrukuj list przewozowy” (w prawym górnym rogu strony zamówienia). Otworzy się okienko, w którym musisz wprowadzić dane do listu przewozowego. Po zapisaniu danych, status zamówienia zmieni się na „Spakowane” i pojawi się do pobrania pdf z listem przewozowym. Ponieważ będziesz przekazywać więcej niż jedną paczkę kurierowi, po wygenerowaniu listów przewozowych dla wszystkich nadawanych tego dnia zamówień, wróć na listę zamówień, zaznacz te zamówienia i wybierz operację „Wystaw zbiorówkę”. Gdy nadasz zamówienie, potwierdź to klikając w „potwierdź wysyłkę” przy danej zbiorówce. Status zamówienia automatycznie zmieni się na „Wysłany”. </li></p>

<p><li><b>Spakowane </b>– Ten status ustawiasz jak skończysz pakować zamówienie i wygenerujesz list przewozowy. Ponieważ będziesz przekazywać więcej niż jedną paczkę kurierowi, po wygenerowaniu listów przewozowych dla wszystkich nadawanych tego dnia zamówień, wróć na listę zamówień, zaznacz te zamówienia i wybierz operację „Wystaw zbiorówkę”. Gdy nadasz zamówienie, potwierdź to klikając w „potwierdź wysyłkę” przy danej zbiorówce. Status zamówienia automatycznie zmieni się na „Wysłany”. </li></p>

<p><li><b>Wysłane</b> – Status ustawiany jest automatycznie, w momencie potwierdzenia wysłania zbiorówki (na stronie Zbiorówki). Gdy klient odbierze przesyłkę, status zmieni się automatycznie na „Dostarczone”.</li></p>

<p><li><b>Dostarczone </b>– Status ustawiany jest automatycznie, w momencie potwierdzenia przez firmę kurierską dostarczenie przesyłki.</li></p>

<p><li><b>Problem</b> – Ten status ustawiasz jeśli pojawił się jakikolwiek problem z realizacją zamówienia. Pamiętaj, żeby skontaktować się z klientem, w celu wyjaśnienia problemu. Warto też wpisać komentarz do zamówienia. Po rozwiązaniu problemu, zmień status na „Skieruj do realizacji”.</li></p>

<p><li><b>Anulowane </b>– Ten status ustawiasz, jeśli z jakichkolwiek powodów trzeba anulować zamówienie – czy to na prośbę klienta czy z winy sklepu. Skontaktuj się z klientem by potwierdzić lub wyjaśnić powód. Warto też wpisać komentarz do zamówienia. W przypadku anulowania przepłaconego zamówienia automatycznie tworzy się nadpłata. Szczegóły widać w "Płatność > Szczegóły płatności". Nadpłatę możesz przypisać do innego nieopłaconego jeszcze płatnego z góry zamówienia tego samego klienta. Aby to zrobić wejdź w zamówienie, do którego chcesz przepisać płatność, kliknij w "Szczegóły płatności" i wybierz opcję "Przydziel" w tabeli nadpłaty. Jeśli w ciągu 24h nie przypiszesz nadpłaty do innego zamówienia, automatycznie wykona się zwrot płatności poprzez system Dotpay, bezpośrednio na konto klienta.</li></p>

<p><li><b>Zwrócony </b>– Status dotyczy zamówień, które nie zostały odebrane przez klienta i zostały zwrócone w całości przez firmę kurierską.</li></p>
</ul></p>

<br/>
<h5>KONTAKT Z KLIENTEM</h5>
<p>Na karcie zamówienia, w sekcji „Kontakt”, masz możliwość napisania wiadomości do klienta. Wystarczy kliknąć w „Napisz wiadomość”, a otworzy się okienko wiadomości. Po wysłaniu wiadomości, zostanie ona też zapisana w sekcji „Historia zmian zamówienia”.  
Jeśli otrzymasz wiadomosć od klienta, zmieni się liczba przy polu „Wiadomości / nowe”. Możesz kliknąć w link „Zobacz wszystkie wiadomości”, aby przejść do listy wszystkich wiadomości od tego klienta. </p>

<br/>
<h5>ALERTY</h5>
<p>Na liście zamówień oraz na karcie zamówienia wyświetlane są alerty – ważne komunikaty, które pojawiają się w sytuacjach, na które warto zwrócić uwagę. </p>
<p><b>Inne zamówienie do tego klienta </b>-  alert zwraca uwagę na większą ilość zamówień do tego samego klienta, co pomaga to wychwycić zdublowane zamówienia. Należy sprawdzić, czy mamy do czynienia z podwojonym zamówieniemi po kontakcie z klientem podwojone zamówienie anulować, połączyć lub realizować oddzielnie. </p>
<p><b>Brak rezerwacji w systemie sprzedawcy </b>– alert zwraca uwagę na brak rezerwacji towaru, co może spowodować problemy w realizacji zamówienia. Alert pojawia się wyłącznie w sytuacji, gdy rezerwacje z Twojego systemu logistycznego są przez API automatycznie synchronizowane z Modago.pl.</p>
<p><b>Błędny kod pocztowy </b>– kod pocztowy, który wpisał klient jest niepoprawny. Sprawdź ten kod i skontaktuj się z klientem jeśli rzeczywiście jest niepoprawny.</p>
<br/>

EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "Panel vendora - Pomoc - Zamówienia - Zbiorówki",
	"identifier" => "udropship-help-pl-udpo-vendor-aggregated",
  "content" => 
<<<EOT
<h4>ZBIORÓWKI</h4>
<p>Zbiorówki generujesz z poziomu listy zamówień, zaznaczając spakowane już zamówienia, które mają wystawione własne listy przewozowe i wybierając operację "Wystaw zbiorówkę" z listy rozwijanej nad tabelą.</p>
<p>W tym miejscu zobaczysz wszystkie wygenerowane już zbiorówki listów przewozowych dla kuriera, możesz je podejrzeć, pobrać w formie pdf i wydrukować. </p>
<p>Po podpisaniu zbiorówki przez kuriera potwierdź ten fakt klikając w pole „Zatwierdź wysyłkę”. Potwierdzenie zbiorówki jest ważne, bo klienci otrzymują w tym momencie informację (maila), że przesyłka została wysłana. </p>
<p>Jeśli chcesz usunąć bądź dodać listy przewozowe do zbiorówki usuń całą zbiorówkę i zrób nową z poziomu listy zamówień. </p>


EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "Panel vendora - Pomoc - Zamówienia - Szczegóły zamówienia",
	"identifier" => "udropship-help-pl-udpo-vendor-edit",
  "content" => 
<<<EOT
<h4>SZCZEGÓŁY ZAMÓWIENIA</h4>
<br/>
<h5>STATUSY ZAMÓWIEŃ</h5>
<p>Na karcie zamówienia, w prawym górnym rogu znajduje się zazwyczaj podpowiedź dotycząca następnego kroku realizacji zamówienia i przycisk zmiany statusu na kolejny. Dodatkowe opcje zmiany statusu (w tym „Problem” i „Anulowane”) są dostępne po kliknięciu w pole aktualnego (w informacjach ogólnych). Niektóre zmiany statusów (zacznij pakowanie, wystaw zbiorówkę, potwierdź rezerwację, skieruj do realizacji) możesz zmienić grupowo z poziomu listy zamówień, jeśli aktualny status tych zamówień na to pozwala. Poniżej objaśnienie statusów:
<ul style="padding-left:20px">

<p><li><b>Oczekuje na potwierdzenie</b>– Nowe zamówienia mogą być zarejestrowane w systemie w statusie „Oczekuje na potwierdzenie”. Dzieje się tak, gdy z zamówieniem związany jest jakiś alert. Alert wyświetlany jest w lewym górnym rogu na czerwono. Alerty są tworzone automatycznie przez system, w sytuacji gdy z jakiegoś powodu zamówienie wyda się „podejrzane” i wymaga decyzji lub wykonania jakiejś czynności lub sprawdzenia przez operatora. Zanim zaakceptujesz zamówienie klikając w przycisk „Akceptuj zamówienie” w prawym górnym rogu strony zamówienia – koniecznie sprawdź czy można to zrobić. </li></p>

<p><li><b>Oczekuje na rezerwację </b>– Status dotyczy zamówień z przedpłatą, informuje o potrzebie rezerwacji towaru pod zamówienie, na czas oczekiwania na płatność. Odłóż zamówione przez klienta produkty do zarezerwowanych, tak żeby były na pewno dostępne i gotowe do wysyłki. Zamówienie nie może być realizowane dopóki nie potwierdzisz dokonania rezerwacji klikając w przycisk „Potwierdź rezerwację produktów” (w prawym górnym rogu strony zamówienia). </li></p>

<p><li><b>Oczekuje na zapłatę</b> – Zamówienie nie jest jeszcze opłacone, nie może więc zostać wysłane, status dotyczy zarezerwowanych, z opcją przedpłaty.</li></p>

<p><li><b>Oczekuje na spakowanie </b>– Zamówienie może zostać wysłane, czeka na spakowanie. Status dotyczy zamówień, które są już opłacone albo za pobraniem. Zamówienia w tym statusie powinny być skompletowane i wysłane jak najszybciej. Kliknij przycisk „Zacznij pakować” (w prawym górnym rogu strony zamówienia), aby zmienić status.</li></p>

<p><li><b>W trakcie pakowania </b>– Ustaw ten status w momencie przystąpienia do pakowania zamówienia. Następnym krokiem będzie wygenerowanie listu przewozowego klikając w przycisk „Wydrukuj list przewozowy” (w prawym górnym rogu strony zamówienia). Otworzy się okienko, w którym musisz wprowadzić dane do listu przewozowego. Po zapisaniu danych, status zamówienia zmieni się na „Spakowane” i pojawi się do pobrania pdf z listem przewozowym. Ponieważ będziesz przekazywać więcej niż jedną paczkę kurierowi, po wygenerowaniu listów przewozowych dla wszystkich nadawanych tego dnia zamówień, wróć na listę zamówień, zaznacz te zamówienia i wybierz operację „Wystaw zbiorówkę”. Gdy nadasz zamówienie, potwierdź to klikając w „potwierdź wysyłkę” przy danej zbiorówce. Status zamówienia automatycznie zmieni się na „Wysłany”. </li></p>

<p><li><b>Spakowane </b>– Ten status ustawiasz jak skończysz pakować zamówienie i wygenerujesz list przewozowy. Ponieważ będziesz przekazywać więcej niż jedną paczkę kurierowi, po wygenerowaniu listów przewozowych dla wszystkich nadawanych tego dnia zamówień, wróć na listę zamówień, zaznacz te zamówienia i wybierz operację „Wystaw zbiorówkę”. Gdy nadasz zamówienie, potwierdź to klikając w „potwierdź wysyłkę” przy danej zbiorówce. Status zamówienia automatycznie zmieni się na „Wysłany”. </li></p>

<p><li><b>Wysłane</b> – Status ustawiany jest automatycznie, w momencie potwierdzenia wysłania zbiorówki (na stronie Zbiorówki). Gdy klient odbierze przesyłkę, status zmieni się automatycznie na „Dostarczone”.</li></p>

<p><li><b>Dostarczone </b>– Status ustawiany jest automatycznie, w momencie potwierdzenia przez firmę kurierską dostarczenie przesyłki.</li></p>

<p><li><b>Problem</b> – Ten status ustawiasz jeśli pojawił się jakikolwiek problem z realizacją zamówienia. Pamiętaj, żeby skontaktować się z klientem, w celu wyjaśnienia problemu. Warto też wpisać komentarz do zamówienia. Po rozwiązaniu problemu, zmień status na „Skieruj do realizacji”.</li></p>

<p><li><b>Anulowane </b>– Ten status ustawiasz, jeśli z jakichkolwiek powodów trzeba anulować zamówienie – czy to na prośbę klienta czy z winy sklepu. Skontaktuj się z klientem by potwierdzić lub wyjaśnić powód. Warto też wpisać komentarz do zamówienia. W przypadku anulowania przepłaconego zamówienia automatycznie tworzy się nadpłata. Szczegóły widać w "Płatność > Szczegóły płatności". Nadpłatę możesz przypisać do innego nieopłaconego jeszcze płatnego z góry zamówienia tego samego klienta. Aby to zrobić wejdź w zamówienie, do którego chcesz przepisać płatność, kliknij w "Szczegóły płatności" i wybierz opcję "Przydziel" w tabeli nadpłaty. Jeśli w ciągu 24h nie przypiszesz nadpłaty do innego zamówienia, automatycznie wykona się zwrot płatności poprzez system Dotpay, bezpośrednio na konto klienta.</li></p>

<p><li><b>Zwrócony </b>– Status dotyczy zamówień, które nie zostały odebrane przez klienta i zostały zwrócone w całości przez firmę kurierską.</li></p>
</ul></p>

<br/>
<h5>KONTAKT Z KLIENTEM</h5>
<p>Na karcie zamówienia, w sekcji „Kontakt”, masz możliwość napisania wiadomości do klienta. Wystarczy kliknąć w „Napisz wiadomość”, a otworzy się okienko wiadomości. Po wysłaniu wiadomości, zostanie ona też zapisana w sekcji „Historia zmian zamówienia”.  
Jeśli otrzymasz wiadomość od klienta, zmieni się liczba przy polu „Wiadomości / nowe”. Możesz kliknąć w link „Zobacz wszystkie wiadomości”, aby przejść do listy wszystkich wiadomości od tego klienta. </p>

<br/>
<h5>ALERTY</h5>
<p>Na liście zamówień oraz na karcie zamówienia wyświetlane są alerty – ważne komunikaty, które pojawiają się w sytuacjach, na które warto zwrócić uwagę. </p>
<p><b>Inne zamówienie do tego klienta </b>-  alert zwraca uwagę na większą ilość zamówień do tego samego klienta, co pomaga to wychwycić zdublowane zamówienia. Należy sprawdzić, czy mamy do czynienia z podwojonym zamówieniemi po kontakcie z klientem podwojone zamówienie anulować, połączyć lub realizować oddzielnie. </p>
<p><b>Brak rezerwacji w systemie sprzedawcy </b>– alert zwraca uwagę na brak rezerwacji towaru, co może spowodować problemy w realizacji zamówienia. Alert pojawia się wyłącznie w sytuacji, gdy rezerwacje z Twojego systemu logistycznego są przez API automatycznie synchronizowane z Modago.pl.</p>
<p><b>Błędny kod pocztowy </b>– kod pocztowy, który wpisał klient jest niepoprawny. Sprawdź ten kod i skontaktuj się z klientem jeśli rzeczywiście jest niepoprawny.</p>
<br/>

<h5>ZWROT PŁATNOŚCI</h5>
W momencie anulowania przepłaconego zamówienia automatycznie tworzy się nadpłata. Nadpłatę możesz przypisać do innego nieopłaconego jeszcze, płatnego z góry zamówienia tego samego klienta. Aby przypisać  płatność wejdź w zamówienie, kliknij w "Szczegóły płatności" i wybierz opcję "Przydziel" w tabeli nadpłaty. Jeśli w ciągu 24h nie przypiszesz nadpłaty do innego zamówienia, automatycznie wykona się zwrot płatności poprzez system Dotpay, bezpośrednio na konto klienta. </li></p>

<br/><br/>

EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "udropship-help-pl-udprod",
	"identifier" => "udropship-help-pl-udprod",
  "content" => 
<<<EOT
udropship-help-pl-udprod
EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "Panel vendora - Pomoc - Produkty - Przegląd atrybutów",
	"identifier" => "udropship-help-pl-udprod-vendor-attributes",
  "content" => 
<<<EOT
<h4>PRZEGLĄD ATRYBUTÓW</h4>

<p>Dla każdej kategorii produktowej zostały określone atrybuty – najważniejsze cechy dla danego typu produktu. Każdy produkt powinien posiadać, poza opisem tekstowym, wypełnione pola atrybutów. Dzięki atrybutom, użytkownik może łatwo wyszukiwać produkty na stronie, filtrując np. po kolorze, stylu czy cechach dodatkowych produktu. Pozwalają one też grupować produkty i tworzyć rożnego rodzaju kampanie specjalne, czy strony tematyczne. </p>

<p>Poniżej możesz obejrzeć listy atrybutów dla kategorii i skopiować je. Pomogą Ci uzupełnić opisy produktów. Dla każdej cechy podany jest słownik, z którego należy wybrać właściwą wartość. Przy każdym atrybucie znajdziesz informację czy jest obowiązkowy – wymagany przez system (cechy wymagane zaznaczone są na niebiesko) oraz informację o rodzaju atrybutu:
<ul style="padding-left:20px">
<p><li><b>Pole wyboru </b>– należy wybrać tylko jedną, najlepiej dopasowaną wartość z dostępnej listy</li></p>
<p><li><b>Pole wielokrotnego wyboru </b>– można wybrać więcej niż jedną wartość z dostępnej listy</li></p>
<p><li><b>Tekst </b>– można wpisać własną wartość </li></p>
</ul>

<p>Jeśli dla danej cechy brakuje istotnej z Twojego punktu widzenia wartości, możesz zaproponować jej wprowadzenie klikając w przycisk „Poproś o wprowadzenie nowej wartości”. Otworzy się okienko, w którym należy wprowadzić proponowaną wartość i potwierdzić wysłanie zgłoszenia. Propozycja trafi do administratora a Ty otrzymasz na maila kopię wiadomości. Administrator podejmie decyzję i skontaktuje się z Tobą z odpowiedzią. 

EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "Panel vendora - Pomoc - Produkty - Zarządzanie zdjęciami",
	"identifier" => "udropship-help-pl-udprod-vendor-image",
  "content" => 
<<<EOT
<h4>ZARZĄDZANIE ZDJĘCIAMI</h4><br/>

<p>Aby dodać zdjęcia do strony produktu:
<ul style="padding-left:20px">
<li>Załaduj zdjęcia na serwer</li>
<li>Zmapuj zdjęcia z produktami</li>
<li>Zatwierdź galerie z nowymi zdjęciami</li>
</ul>

<br/>

        <h5>ŁADOWANIE ZDJĘĆ NA SERWER</h5>
       <p>Zdjęcia można dodawać w zakładce „Dodaj”, kliknij tam przycisk „Ładowanie zdjęć” i dalej ładuj zdjęcia na jeden z  dwóch sposobów: 
<ul style="padding-left:20px">
           <p> <li>przeciągnij myszką obrazy bezpośrednio na obszar okna ładowania zdjęć</li></p> 
    <p>         <li>wybierz opcję „Dodaj pliki” i wskaż obrazy do wgrania z listy plików </li></p> 
        </ul>
    </p>
<p>Możesz obserwować postęp ładowania plików zdjęć w oknie ładowania. Jeśli wskazałaś/eś więcej plików musisz  chwilę zaczekać. Wszystkie załadowane na serwer zdjęcia możesz przeglądać zanim powiążesz je z produktami. W oknie podglądu zdjęć pokazywane są one w formie miniatur. Możesz tam jeszcze zmienić ich nazwy (prawy przycisk myszy otwiera na zdjęciu menu podręczne) i obejrzeć w oryginalnym rozmiarze, klikając w nie dwa razy. </p>

<br/>
<h5>MAPOWANIE OBRAZÓW Z PRODUKTAMI</h5>
    <p>Możesz zmapować zdjęcia z produktami na jeden z dwóch sposobów: poprzez rozpoznanie nazwy pliku lub za pomocą pliku tekstowego. </p>

<br/>
<p>
MAPOWANIE PO NAZWACH
</p>
        Aby obrazek został rozpoznany musi mieć nazwę w następującym formacie:</p>
        <p>
        <code>
            SKU_PRODUKTU.IDENTYFIKATOR.rozszerzenie     np. X2343-AMARANT.3.jpg
        </code> 
        </p>
<p>SKU Produktu - to Twój unikalny, katalogowy kod produktu</p>
<p>Identyfikator - może być dowolnym ciągiem znaków, ponieważ zdjęcia sortowane są w galerii według nazwy, najlepiej użyć identyfikatora by ponumerować zdjęcia</p>
<p>Rozszerzenie - zdjęcia należy wgrywać w formacie JPG lub PNG</p>

<p>Mapowanie po nazwach uruchamia się po naciśnięciu przycisku – w zależności od wybranej opcji „Mapuj po nazwie” lub „Mapuj z pliku”. Po zakończeniu procesu wyświetlane jest podsumowanie, czyli liczba zmapowanych plików, a pliki dodane do galerii zostają usunięte z podglądu. Jeśli chcemy zmapować wybrane zdjęcia, należy je zaznaczyć na liście (klikając na nie myszką i przytrzymując przycisk CTRL), jeśli nie zaznaczymy żadnego obrazka, będą mapowane wszystkie. 
        </p>

<br/>
<p>
MAPOWANIE Z PLIKU
 </p>
<p>        Ten rodzaj mapowania wymaga przygotowania pliku tekstowego w formacie CSV.
        Pierwszy wiersz jest nagłówkiem i musi zawierać następujące pola:      </p>
        <p>
        <code>sku;file;order;label</code>
        </p>
        <ul>
            <li>SKU - to kod produktu
            <li>FILE - nazwa pliku z obrazkiem
            <li>ORDER - kolejność dodania obrazków do produktu (obrazki zawsze są dodawane za już istniejącymi, chyba że je nadpisują)
            <li>LABEL - dowolna etykieta tekstowa obrazka 
        </ul>
        Kolejne wiersze zawierają wartości rozdzielane średnikami. Kolumny posiadające inne nagłówki są ignorowane. 
        </p>
        <p>
       Przy mapowaniu obrazków z pliku istnieje możliwość podpięcia jednego obrazka pod wiele produktów. Po zakończeniu procesu wyświetlana jest ilość zmapowanych produktów, a obrazki są usuwane z podglądu. 
        </p>
        <p>
        Wzór pliku:
        </p>
        <p>
            <pre>
   sku;file;order;label
   6936-AMARANT;picture1.jpg;1;Widok front
   6936-AMARANT;picture2.jpg;2;Widok tył
   123124;picture_test.jpg;1;Na modelce
            </pre>
        </p>
<p>Jeśli w pliku znajdą się dodatkowe kolumny, będą one ignorowane.</p>
          
<br/><h5>ZATWIERDZANIE GALERII</h5>
<p>Zanim wgrane i zmapowane zdjęcia mogą trafić na stronę, trzeba jeszcze zatwierdzić galerię. Po zmapowaniu zdjęć, zobaczysz listę produktów, z którymi zostały zmapowane. Upewnij się, że wszystko wygląda prawidłowo. Wybierz z listy operacji „Zatwierdź galerie” i zapisz operację. Zdjęcia powinny być od tego momentu widoczne na karcie produktu. </p><br/><br/>
EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "Panel vendora - Pomoc - Produkty - Zarządzanie cenami",
	"identifier" => "udropship-help-pl-udprod-vendor-price",
  "content" => 
<<<EOT
<h4>ZARZĄDZANIE CENAMI I DOSTĘPNOŚCIĄ</h4>

<br/>
<h5>Metody ustawiania cen</h5>
<p>Do wyboru masz dwie metody ustawienia cen:
<ul style="padding-left:20px">
<p><li>Z pliku – automatyczne ustawianie cen za pomocą cennika, w formie pliku XML</li></p>
<p><li>Manualna – ręczne ustawienie ceny dla produktu, z poziomu panelu</li></p>
</ul></p>
<p>Dla każdego produktu decydujesz czy cena ma być wprowadzona ręcznie czy pobrana ze wskazanego cennika. Możesz udostępnić nawet kilka cenników (maksymalnie 4), które pozwolą Ci wygodniej zarządzać cenami w różnych sytuacjach, dla różnych produktów. </p>
<p>Jeśli ustawiasz ceny produktów korzystając z cenników możesz poza typem cennika (A, B, C, D) ustawić dodatkowy narzut lub rabat procentowy od cennika. Dla każdego produktu możesz ustawić inny sposób zarządzania ceną, typ cennika i rabat. </p>


<br/><br/>
<h5>ZARZĄDZANIE CENAMI</h5>
<p>W tabeli zarządzania cenami widać wszystkie Twoje produkty wraz z wszystkimi parametrami umożliwiającymi kontrolę i zarządzanie cenami. Ceny można edytować dla każdego produktu oddzielnie lub dla kilku na raz, grupowo. Jeśli chcesz zmienić cenę dla kilku produktów jednocześnie, zaznacz wybrane produkty (klikając w boks na lewo od nazwy produktu), kliknij w przycisk "Operacje na zaznaczonych" nad listą produktów i wybierz "Zmień ceny".</p>
<p>Aby otworzyć okienko zmiany ceny dla pojedynczego produktu, kliknij na któryś z aktywnych elementów (zaznaczonych niebieską czcionką), przy wybranym produkcie lub w przycisk "Zmień ceny", w przypadku zmiany grupowej.</p>
<p>Jeśli zarządzasz cenami manualnie, a produkt posiada warianty (rozmiary), możesz edytować cenę domyślna, bazową lub zmienić ją dla konkretnego wariantu. Aby zobaczyć szczegóły dla wariantów, kliknij w znak "+" na lewo od nazwy produktu. Cenę dla wariantu określa się jako różnicę względem ceny bazowej produktu. Cena bazowa będzie zawsze ceną najniższą, która pozwala określić cenę widoczną na karcie produktu. Jeśli przy wariantach nie są zdefiniowane żadne różnice cenowe, będą one sprzedawane w cenie bazowej. </p>
<p>Jeśli zarządzasz cenami za pomocą cenników, możesz podejrzeć ceny dla poszczególnych cenników, rozwijając szczegóły i otwierając okienko "zmień cenę" np. klikając w kolumnę z źródłem ceny. W tabeli cen zobaczysz ceny dla produktu, z poszczególnych cenników.</p>

<br/>
<h5>CENY PRODUKTÓW W KAMPANII</h5>
<p>Jeśli produkt jest w kampanii promocyjnej lub wyprzedażowej,  to w panelu zarządzania kampaniami (a nie panelu zarządzania cenami) określasz skąd w trakcie trwania kampanii ma być pobierana dla niego cena – możesz wskazać tam typ ceny i rabat. Na stronie zarządzania cenami, w kolumnie „typ ceny” zobaczysz nazwę kampanii, a po rozwinięciu szczegółów (, kliknij w znak "+" na lewo od nazwy produktu) szczegóły związane z definicją ceny. W przypadku produktów będących w aktywnej kampanii, nie ma możliwości modyfikacji cen z poziomu panelu zarządzenia cenami (ta opcja jest wtedy nieaktywna) – ceny zarządzane są z poziomu ustawień kampanii. </p>

<br/>
<h5>NAJWAŻNIEJSZE PARAMETRY CEN</h5>
<p>Wyjaśnienie najważniejszych parametrów:
<ul style="padding-left:20px">
<p><li><b>Źródło ceny</b> – określa sposób wyboru ceny: czy ma obowiązywać wprowadzana ręcznie z panelu czy cena z pobranego cennika. Z listy rozwijanej wybierz opcję „Manualna” lub nazwę cennika, z którego chcesz zaciągnąć cenę. </li></p>

<p><li><b>Narzut/ Rabat </b>– cenę bazową możesz zmodyfikować określając procentowy rabat lub narzut. W przypadku rabatu wpisz znak minus przed wartością, w przypadku narzutu po prostu wpisz samą wartość. </li></p>

<p><li><b>Cena regularna </b>– jest to aktualnie wyświetlana cena bazowa.</li></p>

<p><li><b>Cena przekreślona </b>– jest to cena, która będzie wyświetlać się przy produkcie jako przekreślona, jeśli produkt dodany jest do aktywnej kampanii (Reklama i promocja > Kampanie) – cena przed promocja lub rekomendowana cena producenta. Cenę możesz wprowadzić ręcznie, wybierając opcję „Manualna” pod polem wartości lub zaciągać ją z pliku cennikowego. </li></p>
</ul></p>

<br/>
<h5>DOSTĘPNOŚĆ I STATUSY PRODUKTÓW</h5>
<p>Informacja o dostępności produktów w poszczególnych POS przekazywana jest za pomocą pliku XML. Aby produkt był dostępny w sprzedaży:
<ul>
<li>opis produktu musi być zatwierdzony (Produkty > Zarządzaj opisami produktów) - tylko wtedy będzie można wyświetlić go w serwisie</li>
<li>produkt musi być dostępny w POS - tylko wtedy będzie można dodać go do koszyka</li>
<li>produkt musi być włączony - tylko wtedy będzie można wyświetlić go w serwisie</li>
</ul></p>

<p>Produkty posiadają jeden z trzech statusów:
<ul>
<li>Nowy - są to produkty wgrane plikiem, ale jeszcze nie włączone. Produkt można włączyć dopiero po zatwierdzeniu opisu i ustawieniu cen.</li>
<li>Włączony - jest to status potwierdzający, że dane produktu zostały sprawdzone. Tylko włączone produkty mogą być widoczne w serwisie.</li>
<li>Błędny - jest to status oznaczający jakiś poważny problem w produkcie, niezależny od dostępności, który wyłącza go z serwisu. Należy go wybrać tylko w wyjątkowych sytuacjach. </li></ul>
Status można zmienić zaznaczając produkty i wybierając właściwą opcję pod przyciskiem "Operacje na zaznaczonych" nad listą produktów.
</p>

<p>Jeśli produkt nie jest dostępny w POS (ma zerowe stany magazynowe), otrzymuje w serwisie status "Chwilowo niedostępny" i wyłączona jest możliwość dodawania go do koszyka. <br />
W szczególnych przypadkach, można wstrzymać sprzedaż produktu ręcznie, niezależnie od informacji o ilości produktów na magazynie. Aby wstrzymać sprzedaż produktu ręcznie:
<ul>
<li> zaznacz wybrane produkty (klikając w boks na lewo od nazwy produktu)</li>
<li>kliknij w przycisk "Operacje na zaznaczonych" nad listą produktów i wybierz "Zmień politykę dostępności"</li>
<li>Zmień politykę dostępności z "Zarządzanie automatyczne" na "Wyłączony ręcznie".</li>
</ul>
Aby ponownie włączyć taki produkt, należy zmienić politykę dostępności ponownie na "Zarządzanie automatyczne".
</p>
<br/><br/>

EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "Panel vendora - Pomoc - Produkty - Zarządzanie opisami produktów",
	"identifier" => "udropship-help-pl-udprod-vendor-product",
  "content" => 
<<<EOT
<h4>ZARZĄDZANIE OPISAMI PRODUKTÓW</h4>
<p>Po wybraniu kategorii produktowej, zobaczysz listę swoich produktów. Jeśli nie widzisz jakiejś kategorii, produktów konkretnego sklepu czy producenta, może to oznaczać, że nie zostały one skonfigurowane w Twoim koncie - skontaktuj się z nami aby poszerzyć swoje uprawnienia: support@modago.pl.</br>Z poziomu tabeli możesz wprowadzać zmiany w opisach i atrybutach. </p>

<p>W tabeli możesz zobaczyć wszystkie atrybuty dla kategorii i wypełnić je dla każdego produktu. Jeśli chcesz uprościć widok tabeli, możesz skorzystać z opcji „Dostosuj kolumny” i wybrać jedynie te, na których chcesz w danej chwili pracować. Obowiązkowe atrybuty oznaczone są symbolem czerwonej gwiazdki. </p>

<br/>
<h5>FILTROWANIE, SORTOWANIE</h5>
<p>W tabeli można filtrować po każdym z atrybutów, wpisując słowo ręcznie lub korzystając z listy rozwijanej. Można też skorzystać z opcji „Pokaż filtry własne”, która pozwoli Ci zawęzić listę po zdefiniowanych po swojej stronie parametrach. Można też sortować po wybranej kolumnie, klikając w jej nagłówek. </p>

<br/>
<h5>EDYTOWANIA PÓL</h5>
<p>Wszystkie pola poza zdjęciem, nazwą produktu i SKU oraz statusem są edytowalne z poziomu tabeli. Wystarczy kliknąć w wybraną komórkę by otworzyło się okienko edycji. Jeśli zaznaczono więcej niż jeden produkt (poprzez zaznaczenie boksików na lewo od zdjęcia), w okienku edycji komórki wyświetli się opcja „Zastosuj do wybranych”. Jeśli zostawisz to pole zaznaczone, zmiana zostanie wprowadzona we wszystkich zaznaczonych produktach. Aby odznaczyć wszystkie produkty, wystarczy kliknąć w znak „+” w nagłówku nad boksami zaznaczania.</p>
<p>W przypadku atrybutów, w których należy wybrać tylko jedną wartość, pojawi się lista rozwijana. W przypadku atrybutów z wielokrotnym wyborem pojawi się lista, na której możesz wybrać więcej niż jedną wartość - wystarczy przytrzymać przycisk CTRL przy zaznaczaniu kolejnych opcji. Aby usunąć zaznaczoną wartość, zmień ustawienie na „usuń” i potwierdź ustawienie przyciskiem „Zapisz”. </p>

<br/>
<h5>REGUŁY UZUPEŁNIANIA</h5>
<p>Reguły uzupełniania pozwalają automatycznie uzupełniać cechy produktów na podstawie innych cech, które posiadają.</p>
Aby stworzyć nową regułę automatyczną:
<ul style="padding-left:20px">
<p><li>odfiltruj produkty wg dowolnych cech</li></p>
<p><li>edytując wybrane pole cechy zaznacz checkbox "Zapisz jako regułę"</li></p>
<p><li>kliknij  przycisk "Zapisz"</li></p>
</ul>
<p>W regułach automatycznych zapisze się, że dla produktów o konkretnych cechach (tych wcześniej wyfiltrowanych) należy ustawiać daną wartość innej cechy.</p>

<p>Przykładowo:<br/>
W sukienkach wpisujesz w filtr opisu słowo "ołówkowa" (wyfiltrujesz wszystkie produkty, które w opisie mają to słowo) i uzupełniając cechę Typ sukienki ustawiasz jako wartość "ołówkowa" zapisując to jako regułę automatyczną. Dla kolejnych, nowych produktów będziesz mógł automatycznie ustawić ten typ sukienki dla wszystkich produktów, które mają właściwe słowo w opisie. Pamiętaj, że w opcji „Pokaż filtry własne” możesz filtrować produkty wg cech, które zaimportowaliśmy z Twojego systemu e-commerce.
</p>

<br/>
<h5>ATRYBUTY</h5>
Poniżej opis wybranych, najczęściej występujących pól tabeli:
<ul style="padding-left:20px">
<p><li><b>Zdjęcie </b>- W tabeli widać podgląd głównego zdjęcia produktu oraz informację (w prawym dolnym rogu miniaturki zdjęcia) o liczbie wszystkich zdjęć w galerii produktu. Klikając na zdjęcie, możesz je powiększyć. Możesz filtrować po ilości zdjęć wpisują liczbę "od" i "do". Zdjęcie pomoże Ci zweryfikować poprawność wypełnionych pól. Tego pola nie możesz edytować.</li></p>

<p><li><b>Nazwa produktu wyświetlana w sklepie</b> - To pole zawiera nie tylko nazwę produktu, ale także jego SKU. W polu pod nagłówkiem kolumny możesz filtrować zarówno po nazwie produktu jak i SKU. Tego pola nie możesz edytować.</li></p>

<p><li><b>Status produktu</b> - To pole określa status produktu, zielona ikonka symbolizuje produkty włączone, czerwona ikonka oznacza produkty błędne - wyłączone i niewidoczne na stronie. Niebieska ikonka oznacza produkty nowe, które muszą zostać jeszcze sprawdzone przed włączeniem. Można filtrować po statusie wybierając wartość z pola rozwijanego. Tego pola nie możesz edytować z poziomu tej tabeli, jedynie z poziomu zakładki "Zarządzanie cenami".</li></p>

<p><li><b>Status opisu</b> - To pole określa status opisu produktu, zielona ikonka symbolizuje zatwierdzony, sprawdzony już opis, czerwona ikonka oznacza niezatwierdzony jeszcze opis, który należy sprawdzić i zatwierdzić. W niektórych przypadkach administracja galerii może chcieć zweryfikować opisy - w takim przypadku, po zatwierdzeniu opisu przez sprzedawcę, trafia on jeszcze do weryfikacji (co symbolizuje niebieska ikonka statusu ""Czeka na akceptację admina"). Gdy admin potwierdzi poprawność opisu, zmieni jego status na "Zatwierdzony". Można filtrować po statusie wybierając wartość z pola rozwijanego.</li></p>

<p><li><b>Sklep</b> - Określa nazwę sklepu, do którego przypisany jest produkt. Sklep bardzo często będzie równoznaczny z marką, ale mogą być sytuację kiedy jeden sklep posiada więcej niż jedną pod-markę - np. oddzielną dedykowaną dzieciom czy akcesoriom.</li></p>

<p><li><b>Opis</b> - W tabeli widać fragment opisu. Można najechać na niego myszką, aby wyświetlił się podgląd całego opisu. </li></p>

<p><li><b>Kolor</b> - Określa główny kolor produktu. Jeśli produkt posiada więcej niż dwa kolory należy wybrać kolor dominujący lub wybrać wartość "wielokolorowy". Jest to pole obowiązkowe zawierające tylko podstawowe kolory, dalej w tabeli znajduje się dodatkowa cecha "Szczegóły koloru", w którym można wybrać już bardziej doprecyzowany odcień koloru.</li></p>

<p><li><b>Marka </b>- Określa dokładną nazwę marki produktu.</li></p>
</ul></p>

<p>Każdy nowy produkt musi mieć zatwierdzony opis oraz status "Włączony", aby pojawił się w serwisie jako dostępny w sprzedaży. W zakładce "Zarządzaj opisami zdjęć" możesz edytować i zatwierdzać opisy produktów, nie możesz jednak zmienić statusu produktu. Status produktu zmienia się dopiero po zatwierdzeniu cen z poziomu "Zarządzanie cenami". Opis zatwierdza się tylko raz. W przypadku poważnych błędów w opisie produktu znajdującego się już w sprzedaży, które nie mogą być szybko poprawione, produkt można wyłączyć z poziomu "Zarządzanie cenami". 



<br/><br/>

EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "udropship-help-pl-udqa",
	"identifier" => "udropship-help-pl-udqa",
  "content" => 
<<<EOT
udropship-help-pl-udqa
EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "udropship-help-pl-udqa-vendor",
	"identifier" => "udropship-help-pl-udqa-vendor",
  "content" => 
<<<EOT
udropship-help-pl-udqa-vendor
EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "Panel vendora - Pomoc - Pytania klientów - Szczegóły",
	"identifier" => "udropship-help-pl-udqa-vendor-questionEdit",
  "content" => 
<<<EOT
<h4>ODPOWIADANIE NA ZAPYTANIA KLIENTÓW</h4>
<p>Na wszystkie pytania klientów należy starać się odpowiedzieć jak najszybciej - najlepiej w ciągu 24h.  </p>

<p>Po wpisaniu treści wiadomości, kliknij w „Wyślij wiadomość”. </p>

<p>Nie możesz niestety odpowiedzieć dwa razy na to samo pytanie. Jeśli chcesz skontaktować się z Klientem w sprawie zamówienia, możesz wysłać wiadomość z widoku zamówienia. </p>

<p>Wiadomość, którą otrzyma klient nie będzie podpisana Twoim imieniem i nazwiskiem i nie będzie w niej adresu ani telefonu kontaktowego. Jeśli chcesz żeby klient otrzymał takie dane wpisz je w treść wiadomości.</p>

EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "Panel vendora - Pomoc - Pytania klientów",
	"identifier" => "udropship-help-pl-udqa-vendor-questions",
  "content" => 
<<<EOT
<h4>OBSŁUGA ZAPYTAŃ KLIENTÓW</h4>

<p>W głównym widoku zobaczysz wszystkie zapytania, które trafiły do Ciebie od klientów. W zależności od rodzaju pytania, wypełnione są różne pola:
<ul style="padding-left:20px">
<p><li><b>pytanie o produkt </b>- w przypadku pytań o produkt, widać nazwę i SKU produktu, są to pytania zadanie z poziomu karty produktu</li></p>
<p><li><b>pytanie o zamówienie</b> - w przypadku pytań dotyczących zamówienia, widać numer zamówienia, są to pytania zadane z poziomu widoku zamówienia </li></p>
<p><li><b>pytanie ogólne </b>- nie ma określonego produktu, ani zamówienia, są to pytania zadane przez ogólny formularz kontaktowy</li></p>
</ul>
Przy każdym zapytaniu widać datę wysłania zapytania i datę odpowiedzi, o ile została udzielona. Możesz dla wygody odfiltrować te zamówienia, które pozostają bez odpowiedzi, używając filtru „Czy odpowiedziano”. </p>

<br/>
<h5>ODPOWIADANIE NA ZAPYTANIA KLIENTÓW</h5>
<p>Na wszystkie pytania klientów należy starać się odpowiedzieć jak najszybciej - najlepiej w ciągu 24h. Aby odpowiedzieć na pytanie, musisz wejść w szczegóły zapytania, klikając w „zobacz” przy wybranej pozycji na liście.  </p>
<p>Po wpisaniu treści wiadomości, kliknij w „Wyślij wiadomość”. </p>
<p>Nie możesz niestety odpowiedzieć dwa razy na to samo pytanie. Jeśli chcesz skontaktować się z Klientem w sprawie zamówienia, możesz wysłać wiadomość z widoku zamówienia. </p>
<p>Wiadomość, którą otrzyma klient nie będzie podpisana Twoim imieniem i nazwiskiem i nie będzie w niej adresu ani telefonu kontaktowego. Jeśli chcesz żeby klient otrzymał takie dane wpisz je w treść wiadomości.</p>


EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "Panel vendora - Pomoc - Strona startowa",
	"identifier" => "udropship-help-pl-udropship",
  "content" => 
<<<EOT
<h4>JAK KORZYSTAĆ Z PANELU POMOCY</h4>
<p>Na każdej podstronie panelu administracyjnego, po kliknięciu w przycisk „Pomoc” wysunie się panel (taki jak ten) z kontekstową pomocą. Jeśli więc masz jakieś wątpliwość związane z zawartością strony, nie wiesz w jaki sposób wprowadzić zmianę czy nie rozumiesz jakiegoś oznaczenia, otwórz panel pomocy i poszukaj odpowiedzi na swoje pytania. Staraliśmy się opisać dokładnie poszczególne funkcje i procesy, jeśli jednak po zapoznaniu się z treścią pomocy, nadal masz jakieś wątpliwości, skontaktuj się z nami, a chętnie wszystko wyjaśnimy. </p>

EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "Panel vendora - Pomoc - Ustawienia - GH API",
	"identifier" => "udropship-help-pl-udropship-ghapi",
  "content" => 
<<<EOT
<DIV TYPE=HEADER>
	<P STYLE="margin-bottom: 0.28cm; border-top: none; border-bottom: 1px solid #000000; border-left: none; border-right: none; padding-top: 0cm; padding-bottom: 0.04cm; padding-left: 0cm; padding-right: 0cm">
	<FONT FACE="Times New Roman, serif">Przewodnik po API Galerii
	Handlowej Modago (API_GH)</FONT></P>
	<P STYLE="margin-bottom: 1.15cm"><BR><BR>
	</P>
</DIV>
	<P ALIGN=CENTER STYLE="margin-bottom: 0cm; line-height: 100%"><FONT FACE="Calibri Light, sans-serif"><FONT SIZE=6 STYLE="font-size: 28pt"><I><B>Dokumentacja
	API Galerii Handlowej Modago</B></I></FONT></FONT></P>
<P CLASS="western" STYLE="margin-bottom: 0.28cm"><BR><BR>
</P>
<TABLE WIDTH=610 BORDER=1 BORDERCOLOR="#000000" CELLPADDING=5 CELLSPACING=0 RULES=ROWS>
	<COLGROUP>
		<COL WIDTH=55>
		<COL WIDTH=28>
	</COLGROUP>
	<COLGROUP>
		<COL WIDTH=47>
		<COL WIDTH=141>
	</COLGROUP>
	<COLGROUP>
		<COL WIDTH=4368>
		<COL WIDTH=42>
	</COLGROUP>
	<COLGROUP>
		<COL WIDTH=37>
	</COLGROUP>
	<COLGROUP>
		<COL WIDTH=4369>
	</COLGROUP>
	<COLGROUP>
		<COL WIDTH=170>
	</COLGROUP>
	<TR>
		<TD COLSPAN=9 WIDTH=598 BGCOLOR="#e5e5e5">
			<P STYLE="margin-top: 0.11cm"><FONT FACE="Times New Roman, serif"><FONT SIZE=2 STYLE="font-size: 11pt"><B>Metryka
			dokumentu</B></FONT></FONT></P>
		</TD>
	</TR>
	<TR VALIGN=TOP>
		<TD COLSPAN=2 WIDTH=93 BGCOLOR="#e5e5e5">
			<P STYLE="margin-top: 0.11cm"><FONT FACE="Times New Roman, serif"><FONT SIZE=2 STYLE="font-size: 11pt"><B>Temat</B></FONT></FONT></P>
		</TD>
		<TD COLSPAN=4 WIDTH=259>
			<P STYLE="margin-top: 0.11cm"><FONT FACE="Times New Roman, serif"><FONT SIZE=2 STYLE="font-size: 11pt">Przewodnik
			po API Galerii Handlowej Modago</FONT></FONT></P>
		</TD>
		<TD COLSPAN=2 WIDTH=47 BGCOLOR="#e5e5e5">
			<P ALIGN=CENTER STYLE="margin-top: 0.11cm"><FONT FACE="Times New Roman, serif"><FONT SIZE=2 STYLE="font-size: 11pt"><B>Firma</B></FONT></FONT></P>
		</TD>
		<TD WIDTH=170>
			<P STYLE="margin-top: 0.11cm"><FONT FACE="Times New Roman, serif"><FONT SIZE=2 STYLE="font-size: 11pt">Zolago
			Group Sp. z o.o.</FONT></FONT></P>
		</TD>
	</TR>
	<TR VALIGN=TOP>
		<TD COLSPAN=2 WIDTH=93 BGCOLOR="#e5e5e5">
			<P STYLE="margin-top: 0.11cm"><FONT FACE="Times New Roman, serif"><FONT SIZE=2 STYLE="font-size: 11pt"><B>Autor</B></FONT></FONT></P>
		</TD>
		<TD COLSPAN=7 WIDTH=495>
			<P STYLE="margin-top: 0.11cm"><FONT FACE="Times New Roman, serif"><FONT SIZE=2 STYLE="font-size: 11pt">Stanisław
			Antoniak</FONT></FONT></P>
		</TD>
	</TR>
	<TR>
		<TD COLSPAN=2 WIDTH=93 BGCOLOR="#e5e5e5">
			<P STYLE="margin-top: 0.11cm"><FONT FACE="Times New Roman, serif"><FONT SIZE=2 STYLE="font-size: 11pt"><B>Nr
			wersji</B></FONT></FONT></P>
		</TD>
		<TD COLSPAN=2 WIDTH=198>
			<P STYLE="margin-top: 0.11cm"><FONT FACE="Times New Roman, serif"><FONT SIZE=2 STYLE="font-size: 11pt">1.1</FONT></FONT></P>
		</TD>
		<TD COLSPAN=3 WIDTH=98 BGCOLOR="#e5e5e5">
			<P STYLE="margin-top: 0.11cm"><FONT FACE="Times New Roman, serif"><FONT SIZE=2 STYLE="font-size: 11pt"><B>Data</B></FONT></FONT></P>
		</TD>
		<TD COLSPAN=2 WIDTH=179>
			<P ALIGN=CENTER STYLE="margin-top: 0.11cm"><FONT FACE="Times New Roman, serif"><FONT SIZE=2 STYLE="font-size: 11pt"><B>2015-03-31</B></FONT></FONT></P>
		</TD>
	</TR>
	<TR VALIGN=TOP>
		<TD COLSPAN=2 WIDTH=93 BGCOLOR="#e5e5e5">
			<P STYLE="margin-top: 0.11cm"><FONT FACE="Times New Roman, serif"><FONT SIZE=2 STYLE="font-size: 11pt"><B>Zastrzeżenie</B></FONT></FONT></P>
		</TD>
		<TD COLSPAN=7 WIDTH=495>
			<P STYLE="margin-top: 0.11cm"><FONT FACE="Times New Roman, serif"><FONT SIZE=2 STYLE="font-size: 11pt">do
			użytku przez partnerów Zolago Group</FONT></FONT></P>
		</TD>
	</TR>
	<TR>
		<TD COLSPAN=9 WIDTH=598 BGCOLOR="#e5e5e5">
			<P STYLE="margin-top: 0.11cm"><FONT FACE="Times New Roman, serif"><FONT SIZE=2 STYLE="font-size: 11pt"><B>Historia
			zmian dokumentu</B></FONT></FONT></P>
		</TD>
	</TR>
	<TR VALIGN=TOP>
		<TD WIDTH=55 BGCOLOR="#e6e6e6">
			<P STYLE="margin-top: 0.11cm"><FONT FACE="Times New Roman, serif"><FONT SIZE=2 STYLE="font-size: 11pt"><B>Wersja</B></FONT></FONT></P>
		</TD>
		<TD COLSPAN=2 WIDTH=84 BGCOLOR="#e6e6e6">
			<P STYLE="margin-top: 0.11cm"><FONT FACE="Times New Roman, serif"><FONT SIZE=2 STYLE="font-size: 11pt"><B>Data</B></FONT></FONT></P>
		</TD>
		<TD COLSPAN=2 WIDTH=151 BGCOLOR="#e6e6e6">
			<P STYLE="margin-top: 0.11cm"><FONT FACE="Times New Roman, serif"><FONT SIZE=2 STYLE="font-size: 11pt"><B>Autor</B></FONT></FONT></P>
		</TD>
		<TD COLSPAN=4 WIDTH=278 BGCOLOR="#e6e6e6">
			<P STYLE="margin-top: 0.11cm"><FONT FACE="Times New Roman, serif"><FONT SIZE=2 STYLE="font-size: 11pt"><B>Opis</B></FONT></FONT></P>
		</TD>
	</TR>
	<TR VALIGN=TOP>
		<TD WIDTH=55 HEIGHT=23>
			<P ALIGN=CENTER STYLE="margin-top: 0.11cm"><FONT FACE="Times New Roman, serif">1.0</FONT></P>
		</TD>
		<TD COLSPAN=2 WIDTH=84>
			<P ALIGN=LEFT STYLE="margin-top: 0.11cm"><FONT FACE="Times New Roman, serif">2015-01-19</FONT></P>
		</TD>
		<TD COLSPAN=2 WIDTH=151>
			<P ALIGN=JUSTIFY STYLE="margin-top: 0.11cm"><FONT FACE="Times New Roman, serif">Stanisław
			Antoniak</FONT></P>
		</TD>
		<TD COLSPAN=4 WIDTH=278>
			<P ALIGN=LEFT STYLE="margin-top: 0.11cm"><FONT FACE="Times New Roman, serif">Utworzenie
			dokumentu</FONT></P>
		</TD>
	</TR>
	<TR VALIGN=TOP>
		<TD WIDTH=55 HEIGHT=22>
			<P ALIGN=CENTER STYLE="margin-top: 0.11cm"><FONT FACE="Times New Roman, serif">1.1</FONT></P>
		</TD>
		<TD COLSPAN=2 WIDTH=84>
			<P ALIGN=LEFT STYLE="margin-top: 0.11cm"><FONT FACE="Times New Roman, serif">2015-03-31</FONT></P>
		</TD>
		<TD COLSPAN=2 WIDTH=151>
			<P ALIGN=JUSTIFY STYLE="margin-top: 0.11cm"><FONT FACE="Times New Roman, serif">Przemysław
			Siwik</FONT></P>
		</TD>
		<TD COLSPAN=4 WIDTH=278>
			<P ALIGN=LEFT STYLE="margin-top: 0.11cm"><FONT FACE="Times New Roman, serif">Uaktualnienie
			dokumentu</FONT></P>
		</TD>
	</TR>
</TABLE>
<H1 CLASS="nagłówek-spisu-treści-western">Spis treści</H1>
<DIV ID="Spis treści1" DIR="LTR">
	<P STYLE="margin-bottom: 0.14cm"><A HREF="#__RefHeading___Toc409469988">Praca
	z API Galerii Handlowej	2</A></P>
	<P STYLE="margin-left: 0.39cm; margin-bottom: 0.14cm"><A HREF="#__RefHeading___Toc409469989">Co
	jest możliwe z API Galerii Handlowej	2</A></P>
	<P STYLE="margin-left: 0.39cm; margin-bottom: 0.14cm"><A HREF="#__RefHeading___Toc409469990">Budowa
	procesów biznesowych i scenariuszy korzystania z API	2</A></P>
	<P STYLE="margin-left: 0.39cm; margin-bottom: 0.14cm"><A HREF="#__RefHeading___Toc409469991">Przykładowe
	scenariusze korzystania z API Galerii Handlowej	2</A></P>
	<P STYLE="margin-left: 0.78cm; margin-bottom: 0.14cm"><A HREF="#__RefHeading___Toc409469992">I.
	Kompletacja i list przewozowy w systemie partnera.	2</A></P>
	<P STYLE="margin-left: 0.78cm; margin-bottom: 0.14cm"><A HREF="#__RefHeading__22_1444726233">II.
	Kompletacja i list przewozowy w systemie Galerii Handlowej,
	automatyzacja rezerwacji, wydruku paragonów i rozchodów w systemie
	partnera. 	3</A></P>
	<P STYLE="margin-bottom: 0.14cm"><A HREF="#__RefHeading___Toc409469994">Opis
	metod	4</A></P>
	<P STYLE="margin-left: 0.39cm; margin-bottom: 0.14cm"><A HREF="#__RefHeading___Toc409469995">Logowanie
	do API	4</A></P>
	<P STYLE="margin-left: 0.39cm; margin-bottom: 0.14cm"><A HREF="#__RefHeading__24_1444726233">Pobranie
	listy komunikatów 	4</A></P>
	<P STYLE="margin-left: 0.39cm; margin-bottom: 0.14cm"><A HREF="#__RefHeading___Toc409469997">Potwierdzenie
	wykonania komunikatów	5</A></P>
	<P STYLE="margin-left: 0.39cm; margin-bottom: 0.14cm"><A HREF="#__RefHeading___Toc409469998">Pobranie
	zawartości zamówień	5</A></P>
	<P STYLE="margin-left: 0.39cm; margin-bottom: 0.14cm"><A HREF="#__RefHeading___Toc409469999">Przekazanie
	informacji o spakowaniu przesyłki	8</A></P>
	<P STYLE="margin-left: 0.39cm; margin-bottom: 0.14cm"><A HREF="#__RefHeading___Toc409470000">Przekazanie
	danych o wysyłce	8</A></P>
	<P STYLE="margin-left: 0.39cm; margin-bottom: 0.14cm"><A HREF="#__RefHeading___Toc409470001">Przekazanie
	danych o rezerwacji	8</A></P>
	<P STYLE="margin-bottom: 0.14cm"><A HREF="#__RefHeading___Toc409470002">Konfiguracja
	API	9</A></P>
	<P STYLE="margin-left: 0.39cm; margin-bottom: 0.14cm"><A HREF="#__RefHeading___Toc409470003">Konfiguracja
	techniczna	9</A></P>
	<P STYLE="margin-left: 0.39cm; margin-bottom: 0.14cm"><A HREF="#__RefHeading___Toc409470004">Konfiguracja
	biznesowa	9</A></P>
	<P STYLE="margin-left: 0.39cm; margin-bottom: 0.14cm"><A HREF="#__RefHeading___Toc409470005">Rezerwacje
	a dostępność produktów partnera w Galerii Handlowej	10</A></P>
	<P STYLE="margin-bottom: 0.14cm"><A HREF="#__RefHeading___Toc409470006">Wsparcie
	techniczne	11</A></P>
</DIV>
<P CLASS="western" STYLE="margin-bottom: 0.28cm"><BR><BR>
</P>
<H1 CLASS="western" STYLE="page-break-before: always"><A NAME="__RefHeading___Toc409469988"></A>
Praca z API Galerii Handlowej</H1>
<H2 CLASS="western"><A NAME="__RefHeading___Toc409469989"></A>Co jest
możliwe z API Galerii Handlowej</H2>
<P CLASS="western" STYLE="margin-left: 0.64cm; margin-bottom: 0.28cm">
API Galerii Handlowej pozwala partnerowi automatycznie pobrać
zamówienia złożone w Galerii Handlowej po to żeby realizować je we
własnym systemie logistycznym lub sprzedażowym oraz zwrotnie
zaktualizować w Galerii Handlowej statusy i dane wynikające z
realizacji zamówień.</P>
<H2 CLASS="western" STYLE="font-style: normal"><A NAME="__RefHeading___Toc409469990"></A>
Budowa procesów biznesowych i scenariuszy korzystania z API</H2>
<P CLASS="western" STYLE="margin-bottom: 0.28cm">Dostępne metody
komunikacji dają dużą swobodę w realizacji procesów realizacji
zamówień. Obsługa zamówień do momentu decyzji o wysyłce produktu musi
być wykonywana w panelu sprzedawcy Galerii Handlowej, ale kompletacja
oraz drukowanie listów przewozowych i ekspedycja wysyłek może być
realizowana zarówno w systemie partnera jak i w panelu sprzedawcy
Galerii Handlowej.</P>
<P CLASS="western" STYLE="margin-bottom: 0.28cm">W obecnej wersji API
nie udostępniamy metod do wykonywania dowolnych zmian danych w
zamówieniach. Dlatego wszelkie zmiany cen, adresów, produktów na
zamówieniu, anulowanie zamówień muszą być wykonane w panelu www
sprzedawcy Galerii Handlowej. Stamtąd dopiero zmienione zamówienia
mogą być jeszcze raz automatycznie pobrane przez API do systemu
partnera, po to żeby je skompletować, spakować i wyekspediować do
klienta. Wymagamy, aby w panelu sprzedawcy Galerii Handlowej
wprowadzane były wszystkie zmiany w zamówieniach dotyczące adresów,
produktów i cen. Muszą one być aktualne gdyż są one podstawą w
procesach posprzedażnych (zwrotach i reklamacjach). Są one także
prezentowane klientowi w historii zamówień. Klient musi widzieć
aktualne dane. 
</P>
<P CLASS="western" STYLE="margin-bottom: 0.28cm">W przypadku, gdy
partner realizuje wysyłkę ze swojego systemu, wymagamy, aby przekazał
przez API informację o tym, że przesyłka została wysłana do Klienta
oraz informację o spedytorze i numerze listu przewozowego. Klient
otrzymuje dalej te informacje mailem i może dzięki temu zorientować
się gdzie jest jego przesyłka. Później służą systemowi Galerii
Handlowej do śledzenia przesyłki i rozliczenia transakcji z partnerem
i są cały czas prezentowane klientowi w historii jego zakupów.</P>
<P CLASS="western" STYLE="margin-bottom: 0.28cm">Od decyzji (i
możliwości technicznych) partnera zależy, czy będzie przesyłał do
systemu Galerii Handlowej informację o rezerwacjach. Informacja o
rezerwacjach nie jest prezentowana klientom, wspomaga jedynie obsługę
zamówień (w zamówieniu widać informację o rezerwacji, jeśli jest brak
rezerwacji to zamówienie jest automatycznie wstrzymywane).</P>
<P CLASS="western" STYLE="margin-bottom: 0.28cm">Poniżej opisane
zostaną przykładowe, najbardziej prawdopodobne scenariusze realizacji
procesów realizacji zamówień. 
</P>
<H2 CLASS="western"><A NAME="__RefHeading___Toc409469991"></A>Przykładowe
scenariusze korzystania z API Galerii Handlowej</H2>
<H3 CLASS="western"><A NAME="__RefHeading___Toc409469992"></A>I.
Kompletacja i list przewozowy w systemie partnera.</H3>
<P CLASS="western" STYLE="margin-bottom: 0.28cm">Proces realizacji
zamówienia do momentu skierowania do kompletacji i wysyłki odbywa się
w systemie Galerii Handlowej. To znaczy modyfikacje zamówienia
(adresy, produkty) wykonywane są w systemie Galerii Handlowej a
kompletacja produktów i drukuj list przewozowy w systemie
logistyczno-sprzedażowym partnera.</P>
<P CLASS="western" STYLE="margin-bottom: 0.28cm">W tym scenariuszu
należy korzystać z 6 metod API. Pierwszą metodą
(<I>getChangeOrderMessage</I>) można pobrać komunikaty o nowych
zamówieniach i zmianach w zamówieniach (zmiany wykonywane są ręcznie
w panelu sprzedawcy Galerii Handlowej). Metoda przekazuje listę
identyfikatorów zamówień wraz z informacją jakiego rodzaju jest
zmiana. Osobną metodą (<I>getOrdersByID</I>) można pobrać zawartość
zamówień i wykonać przetwarzanie w swoim systemie w zależności od
rodzaju zmiany przekazanej komunikatem. Po wykonaniu przetwarzania w
swoim systemie należy potwierdzić wykonanie komunikatu poprzez
wywołanie metody <I>setChangeOrderMessageConfirmation.</I> Dopóki
komunikat nie zostanie potwierdzony informacja o zmianie będzie
ciągle przekazywany przy kolejnych wywołaniach metody 
<I>getChangeOrderMessage. </I>
</P>
<P CLASS="western" STYLE="margin-bottom: 0.28cm">Zamówienia, które
mają status &bdquo;ready&rdquo; i są w pełni opłacone (lub mają
metodę płatności &bdquo;cash_on_delivery&rdquo;) mogą być
kompletowane i przygotowywane do wysyłki w systemie partnera.
Zalecamy, żeby zawsze w ostatnim punkcie kontroli zamówień w systemie
partnera wykonać sprawdzenie w systemie Galerii Handlowej (odczyt
danych zamówienia) czy zamówienie spełnia warunki realizacji (status
&bdquo;ready&rdquo;, kompletna płatność).</P>
<P CLASS="western" STYLE="margin-bottom: 0.28cm">W procesie tym
należy przekazać informację o spakowaniu zamówienia
(<I>setOrderAsCollected) </I>w drugą stronę (od systemu partnera do
systemu Galerii Handlowej), a następnie informację o wysyłce
zamówienia oraz dane związane z tą wysyłką, takie jak oznaczenie
kuriera oraz numer listu przewozowego. Do tego służy metoda
<I>setOrderShipment</I>.</P>
<P CLASS="western" STYLE="margin-bottom: 0.28cm">Dodatkowo można
przekazywać do systemu Galerii Handlowej informację o rezerwacji
produktów do zamówienia. Służy do tego metoda setOrderReservation.
Ustawia ona w zamówieniu informację o statusie rezerwacji. Dzięki
temu operator obsługujący zamówienia klientów będzie mógł zareagować
na problemy z rezerwacją komunikując się z klientem i ustalając z nim
zmiany w zamówieniu.</P>
<P CLASS="western" STYLE="margin-bottom: 0.28cm">Więcej o przesyłaniu
rezerwacji do zamówienia można dowiedzieć się w rozdziale Rezerwacje a dostępność produktów partnera w Galerii Handlowej</P>
<H3 CLASS="western"><A NAME="__RefHeading___Toc409469993"></A><A NAME="__RefHeading__22_1444726233"></A>
II. Kompletacja i list przewozowy w systemie Galerii Handlowej,
automatyzacja rezerwacji, wydruku paragonów i rozchodów w systemie
partnera. 
</H3>
<P CLASS="western" STYLE="margin-bottom: 0.28cm">Modyfikacje
zamówienia (adresy, produkty), kompletacja produktów i wydruk listu
przewozowego wykonuje się w systemie Galerii Handlowej a w swoim
systemie partner wykonuje rezerwacje i przesyła o nich informację do
Galerii Handlowej. Po przygotowaniu zamówienia może zautomatyzować
proces wydruku paragonów w swoim systemie lub/i rozchodowania towaru
w magazynie.</P>
<P CLASS="western" STYLE="margin-bottom: 0.28cm">W tym scenariuszu
wystarczy korzystać z 4 metod API, nieco inaczej niż w scenariuszu I.
</P>
<P CLASS="western" STYLE="margin-bottom: 0.28cm">Pierwszą metodą
(<I>getChangeOrderMessage</I>) można pobrać komunikaty o nowych
zamówieniach i zmianach w zamówieniach (zmiany wykonywane są ręcznie
w panelu sprzedawcy Galerii Handlowej). Ponieważ do wykonania
poprawnej rezerwacji w systemie logistycznym potrzebujemy tylko
produktów i statusu zamówienia należy  w konfiguracji API wyłączyć
informowanie o wszelkich innych zmianach (należy pozostawić włączone
typy komunikatów <I>newOrder, cancelledOrder</I> i <I>itemsChanged</I>).
Informację o zamówieniu należy pobrać tak jak w scenariuszu I metodą 
<I>getOrdersByID. </I>
</P>
<P CLASS="western" STYLE="margin-bottom: 0.28cm">Po pobraniu
komunikatów oraz zawartości zamówień i wykonaniu rezerwacji w swoim
systemie należy przekazać do Galerii Handlowej status rezerwacji.
Służy do tego metoda setOrderReservation. Ustawia ona w zamówieniu
informację o statusie rezerwacji. Dzięki temu operator obsługujący
zamówienia klientów będzie mógł zareagować na problemy z rezerwacją
komunikując się z klientem i ustalając z nim zmiany w zamówieniu. 
</P>
<P CLASS="western" STYLE="margin-bottom: 0.28cm">Po przygotowaniu
przesyłek w systemie Galerii Handlowej można pobrać zamówienia do
systemu Partnera aby wystawić w nim paragony i wykonać rozchód
towaru. Zamówienia takie mają w Galerii status &bdquo;ready&rdquo; (w
metodzie  <I>getOrdersByID</I>). 
</P>
<P CLASS="western" STYLE="margin-bottom: 0.28cm"><BR><BR>
</P>
<P CLASS="western" STYLE="margin-left: 0.64cm; margin-bottom: 0.28cm">
<BR><BR>
</P>
<P CLASS="western" STYLE="margin-left: 0.64cm; margin-bottom: 0.28cm">
<BR><BR>
</P>
<P CLASS="western" STYLE="margin-left: 0.64cm; margin-bottom: 0.28cm">
<BR><BR>
</P>
<H1 CLASS="western" STYLE="page-break-before: always"><A NAME="__RefHeading___Toc409469994"></A>
Opis metod</H1>
<P CLASS="western" STYLE="margin-bottom: 0.28cm">API_GH to <FONT COLOR="#0563c1"><U><A HREF="http://en.wikipedia.org/wiki/Web_service" TARGET="_blank">usługa
sieciowa</A></U></FONT> opierająca swoje działanie na <FONT COLOR="#0563c1"><U><A HREF="http://en.wikipedia.org/wiki/SOAP" TARGET="_blank">protokole
SOAP</A></U></FONT>, wykorzystująca <FONT COLOR="#0563c1"><U><A HREF="http://en.wikipedia.org/wiki/XML" TARGET="_blank">język
XML</A></U></FONT> jako format tworzenia. Usługa jest zgodna z
obecnie obowiązującymi standardami SOAP.</P>
<H2 CLASS="western" STYLE="font-style: normal"><A NAME="__RefHeading___Toc409469995"></A>
Logowanie do API</H2>
<P CLASS="western" STYLE="margin-left: 0.64cm; margin-bottom: 0.28cm">
<FONT SIZE=3>Funkcja: </FONT><FONT FACE="Courier 10 Pitch"><FONT SIZE=3>doLogin</FONT></FONT></P>
<P CLASS="western" STYLE="margin-left: 0.64cm; margin-bottom: 0.28cm">
<FONT SIZE=3>	parametry:</FONT></P>
<P CLASS="western" STYLE="margin-left: 0.64cm; margin-bottom: 0.28cm">
<FONT SIZE=3>		</FONT><FONT FACE="Courier 10 Pitch"><FONT SIZE=3>int
vendorId</FONT></FONT><FONT SIZE=3>, </FONT>
</P>
<P CLASS="western" STYLE="margin-left: 0.64cm; margin-bottom: 0.28cm">
<FONT SIZE=3>		</FONT><FONT FACE="Courier 10 Pitch"><FONT SIZE=3>string
password</FONT></FONT><FONT SIZE=3>,</FONT></P>
<P CLASS="western" STYLE="margin-left: 0.64cm; margin-bottom: 0.28cm">
<FONT SIZE=3>		</FONT><FONT FACE="Courier 10 Pitch"><FONT SIZE=3>string
webApiKey</FONT></FONT></P>
<P CLASS="western" STYLE="margin-left: 1.25cm; margin-bottom: 0.28cm">
Metoda loguje do systemu i zwraca  <I>sessionToken</I>, który służy
dalej do uwierzytelniania przy wywołaniach pozostałych metod. 
</P>
<P CLASS="western" STYLE="margin-left: 1.25cm; margin-bottom: 0.28cm">
Parametry wywołania do tej metody możesz pobrać z konfiguracji API w
panelu sprzedawcy (konfiguracja API opisana jest w dalszej części
dokumentu). 
</P>
<P CLASS="western" STYLE="margin-left: 1.25cm; margin-bottom: 0.28cm">
Metoda może zwracać kilka rodzajów błędów:</P>
<P CLASS="western" STYLE="margin-left: 1.25cm; margin-bottom: 0.28cm">
&bdquo;error_vendor_inactive&rdquo; - niepoprawna wartość
identyfikatora sprzedawcy lub nieaktywny sprzedawca</P>
<P CLASS="western" STYLE="margin-left: 1.25cm; margin-bottom: 0.28cm">
&bdquo;error_password_invalid&rdquo; - niepoprawne hasło lub hasło
niezdefiniowane w panelu sprzedawcy</P>
<P CLASS="western" STYLE="margin-left: 1.25cm; margin-bottom: 0.28cm">
&bdquo;error_webapikey_invalid&rdquo; - niepoprawny lub nieaktywny
klucz WebAPI</P>
<P CLASS="western" STYLE="margin-left: 1.25cm; margin-bottom: 0.28cm">
Zwracany sessionToken jest ważny przez godzinę. Po upłynięciu tego
czasu wszystkie pozostałe metody będą zwracały błąd
&bdquo;error_session_token_invalid&rdquo;.</P>
<H2 CLASS="western"><A NAME="__RefHeading___Toc409469996"></A><A NAME="__RefHeading__24_1444726233"></A>
<SPAN STYLE="font-style: normal">Pobranie listy komunikatów </SPAN>
</H2>
<P CLASS="western" STYLE="margin-left: 0.64cm; margin-bottom: 0.28cm">
<FONT SIZE=3>Funkcja: </FONT><FONT FACE="Courier 10 Pitch"><FONT SIZE=3>getChangeOrderMessage</FONT></FONT></P>
<P CLASS="western" STYLE="margin-left: 0.64cm; margin-bottom: 0.28cm">
<FONT SIZE=3>	parametry:</FONT></P>
<P CLASS="western" STYLE="margin-left: 0.64cm; margin-bottom: 0.28cm">
<FONT SIZE=3>		</FONT><FONT FACE="Courier 10 Pitch"><FONT SIZE=3>string
sessionToken</FONT></FONT><FONT SIZE=3>, </FONT>
</P>
<P CLASS="western" STYLE="margin-left: 0.64cm; margin-bottom: 0.28cm">
<FONT SIZE=3>		</FONT><FONT FACE="Courier 10 Pitch"><FONT SIZE=3>int
messageBatchSize, </FONT></FONT>
</P>
<P CLASS="western" STYLE="margin-left: 0.64cm; margin-bottom: 0.28cm">
<FONT FACE="Courier 10 Pitch"><FONT SIZE=3>		string messageType </FONT></FONT>
</P>
<P CLASS="western" STYLE="margin-left: 1.25cm; margin-bottom: 0.28cm">
Metoda zwraca pierwszych <I>messageBatchSize </I>komunikatów o
zmianie zamówień. Dodatkowo można zawęzić komunikaty do określonego
typu. 
</P>
<P CLASS="western" STYLE="margin-left: 1.25cm; margin-bottom: 0.28cm">
Pojedynczy komunikat ma strukturę: 
</P>
<P CLASS="western" STYLE="margin-left: 2.5cm; margin-bottom: 0.28cm">int
messageID</P>
<P CLASS="western" STYLE="margin-left: 2.5cm; margin-bottom: 0.28cm">string
messageType</P>
<P CLASS="western" STYLE="margin-left: 2.5cm; margin-bottom: 0.28cm">string
orderID</P>
<P CLASS="western" STYLE="margin-left: 1.25cm; margin-bottom: 0.28cm">
Możliwe typy komunikatów opisane są w tabeli</P>
<TABLE WIDTH=602 BORDER=1 BORDERCOLOR="#000000" CELLPADDING=4 CELLSPACING=0>
	<COL WIDTH=175>
	<COL WIDTH=409>
	<TR VALIGN=TOP>
		<TD WIDTH=175>
			<P CLASS="western" STYLE="margin-left: 0.55cm; margin-right: -0.15cm">
			Komunikat</P>
		</TD>
		<TD WIDTH=409>
			<P CLASS="western" STYLE="margin-left: 0.47cm; margin-right: 0.19cm">
			Komunikat jest wyzwalany po zmianie następujących danych w
			zamówieniu</P>
		</TD>
	</TR>
	<TR VALIGN=TOP>
		<TD WIDTH=175>
			<P CLASS="western" STYLE="margin-left: 0.55cm; margin-right: -0.15cm">
			newOrder</P>
		</TD>
		<TD WIDTH=409>
			<P CLASS="western" STYLE="margin-left: 0.47cm; margin-right: 0.19cm">
			Pojawiło się nowe zamówienie do realizacji.</P>
		</TD>
	</TR>
	<TR VALIGN=TOP>
		<TD WIDTH=175>
			<P CLASS="western" STYLE="margin-left: 0.55cm; margin-right: -0.15cm">
			cancelledOrder</P>
		</TD>
		<TD WIDTH=409>
			<P CLASS="western" STYLE="margin-left: 0.47cm; margin-right: 0.19cm">
			Zamówienie zostało anulowane.</P>
		</TD>
	</TR>
	<TR VALIGN=TOP>
		<TD WIDTH=175>
			<P CLASS="western" STYLE="margin-left: 0.55cm; margin-right: -0.15cm">
			paymentDataChanged</P>
		</TD>
		<TD WIDTH=409>
			<P CLASS="western" STYLE="margin-left: 0.47cm; margin-right: 0.19cm">
			Zmieniła się płatność do zamówienia (np. zamówienie zostało
			opłacone) i/lub Zmieniła się metoda płatności w zamówienia.</P>
		</TD>
	</TR>
	<TR VALIGN=TOP>
		<TD WIDTH=175>
			<P CLASS="western" STYLE="margin-left: 0.55cm; margin-right: -0.15cm">
			itemsChanged 
			</P>
		</TD>
		<TD WIDTH=409>
			<P CLASS="western" STYLE="margin-left: 0.47cm; margin-right: 0.19cm">
			Zmieniły się produkty lub koszt dostawy w zamówieniu. Produkty
			mogły być usunięte, dodane, zmieniona cena, ilość, rabat. 
			</P>
		</TD>
	</TR>
	<TR VALIGN=TOP>
		<TD WIDTH=175>
			<P CLASS="western" STYLE="margin-left: 0.55cm; margin-right: -0.15cm">
			deliveryDataChanged</P>
		</TD>
		<TD WIDTH=409>
			<P CLASS="western" STYLE="margin-left: 0.47cm; margin-right: 0.19cm">
			Zmieniła się metoda dostawy lub/i adres dostawy. 
			</P>
		</TD>
	</TR>
	<TR VALIGN=TOP>
		<TD WIDTH=175>
			<P CLASS="western" STYLE="margin-left: 0.55cm; margin-right: -0.15cm">
			invoiceAddressChanged</P>
		</TD>
		<TD WIDTH=409>
			<P CLASS="western" STYLE="margin-left: 0.47cm; margin-right: 0.19cm">
			Zmieniły się dane do faktury lub/i informacja czy faktura jest
			wymagana.</P>
		</TD>
	</TR>
	<TR VALIGN=TOP>
		<TD WIDTH=175>
			<P CLASS="western" STYLE="margin-left: 0.55cm; margin-right: -0.15cm">
			statusChanged</P>
		</TD>
		<TD WIDTH=409>
			<P CLASS="western" STYLE="margin-left: 0.47cm; margin-right: 0.19cm">
			Zmienił się status zamówienia. 
			</P>
		</TD>
	</TR>
</TABLE>
<P CLASS="western" STYLE="margin-left: 1.25cm; margin-bottom: 0.28cm">
<BR><BR>
</P>
<H2 CLASS="western" STYLE="font-style: normal"><A NAME="__RefHeading___Toc409469997"></A>
Potwierdzenie wykonania komunikatów</H2>
<P CLASS="western" STYLE="margin-left: 0.64cm; margin-bottom: 0.28cm">
<FONT SIZE=3>Funkcja: </FONT><FONT FACE="Courier 10 Pitch"><FONT SIZE=3>setChangeOrderMessageConfimation</FONT></FONT></P>
<P CLASS="western" STYLE="margin-left: 0.64cm; margin-bottom: 0.28cm">
<FONT FACE="Calibri, sans-serif"><FONT SIZE=3>	parametry:</FONT></FONT></P>
<P CLASS="western" STYLE="margin-left: 0.64cm; margin-bottom: 0.28cm">
<FONT FACE="Courier 10 Pitch"><FONT SIZE=3>		string sessionToken</FONT></FONT></P>
<P CLASS="western" STYLE="margin-left: 0.64cm; margin-bottom: 0.28cm">
<FONT FACE="Courier 10 Pitch"><FONT SIZE=3>		int[] messageID</FONT></FONT></P>
<P CLASS="western" STYLE="margin-left: 1.25cm; margin-bottom: 0.28cm">
Metoda oznacza przekazane w parametrze komunikaty jako zrealizowane.
Ponowne odczytanie listy komunikatów metodą <I>getChangeOrderMessage</I>
nie będzie ich już zawierało. 
</P>
<H2 CLASS="western" STYLE="font-style: normal"><A NAME="__RefHeading___Toc409469998"></A>
Pobranie zawartości zamówień</H2>
<P CLASS="western" STYLE="margin-left: 0.64cm; margin-bottom: 0.28cm">
<FONT SIZE=3>Funkcja: </FONT><FONT FACE="Courier 10 Pitch"><FONT SIZE=3>getOrdersByID</FONT></FONT><FONT SIZE=3>
</FONT>
</P>
<P CLASS="western" STYLE="margin-left: 0.64cm; margin-bottom: 0.28cm">
<FONT SIZE=3>	parametry:</FONT></P>
<P CLASS="western" STYLE="margin-left: 0.64cm; margin-bottom: 0.28cm">
<FONT FACE="Courier 10 Pitch"><FONT SIZE=3>	string sessionToken</FONT></FONT></P>
<P CLASS="western" STYLE="margin-left: 0.64cm; margin-bottom: 0.28cm">
<FONT FACE="Courier 10 Pitch"><FONT SIZE=3>	string[] orderID</FONT></FONT></P>
<P CLASS="western" STYLE="margin-left: 0.64cm; margin-bottom: 0.28cm">
 
</P>
<P CLASS="western" STYLE="margin-left: 1.25cm; margin-bottom: 0.28cm">
Pokazuje zamówienia o podanym ID (można podać listę ID). 
</P>
<P CLASS="western" STYLE="margin-left: 1.25cm; margin-bottom: 0.28cm">
Pojedyncze zamówienie jest zwrotnie przekazywane jest w następującej
strukturze:</P>
<TABLE WIDTH=606 BORDER=1 BORDERCOLOR="#000000" CELLPADDING=4 CELLSPACING=0>
	<COL WIDTH=274>
	<COL WIDTH=314>
	<TR VALIGN=TOP>
		<TD WIDTH=274>
			<P>Element struktury</P>
		</TD>
		<TD WIDTH=314>
			<P>Opis</P>
		</TD>
	</TR>
	<TR VALIGN=TOP>
		<TD WIDTH=274>
			<P>vendor_id, vendor_name</P>
		</TD>
		<TD WIDTH=314>
			<P>Kod i nazwa sprzedawcy</P>
		</TD>
	</TR>
	<TR VALIGN=TOP>
		<TD WIDTH=274>
			<P>order_id</P>
		</TD>
		<TD WIDTH=314>
			<P>Numer zamówienia</P>
		</TD>
	</TR>
	<TR VALIGN=TOP>
		<TD WIDTH=274>
			<P>order_date</P>
		</TD>
		<TD WIDTH=314>
			<P>Data złożenia zamówienia</P>
		</TD>
	</TR>
	<TR VALIGN=TOP>
		<TD WIDTH=274>
			<P>order_max_shipping_date</P>
		</TD>
		<TD WIDTH=314>
			<P>Maksymalna data realizacji</P>
		</TD>
	</TR>
	<TR VALIGN=TOP>
		<TD WIDTH=274>
			<P>order_status</P>
		</TD>
		<TD WIDTH=314>
			<P STYLE="margin-bottom: 0.28cm; widows: 2; orphans: 2"><FONT SIZE=2 STYLE="font-size: 11pt">Status
			zamówienia. Możliwe są następujące statusy:</FONT></P>
			<P STYLE="margin-bottom: 0.28cm; widows: 2; orphans: 2"><FONT SIZE=2 STYLE="font-size: 11pt">pending
			&ndash; zamówienie wstrzymane, problematyczne lub oczekujące na
			potwierdzenie </FONT>
			</P>
			<P STYLE="margin-bottom: 0.28cm; widows: 2; orphans: 2"><FONT SIZE=2 STYLE="font-size: 11pt">pending_payment
			&ndash; zamówienie oczekujące na płatność klienta</FONT></P>
			<P STYLE="margin-bottom: 0.28cm; widows: 2; orphans: 2"><FONT SIZE=2 STYLE="font-size: 11pt">ready
			&ndash; zamówienie jest gotowe do realizacji, można zebrać towar i
			przygotować przesyłkę</FONT></P>
			<P STYLE="margin-bottom: 0.28cm; widows: 2; orphans: 2"><FONT SIZE=2 STYLE="font-size: 11pt">shipped
			&ndash; wysłane do klienta (przesyłkę przejął kurier)</FONT></P>
			<P STYLE="margin-bottom: 0.28cm; widows: 2; orphans: 2"><FONT SIZE=2 STYLE="font-size: 11pt">delivered
			&ndash; dostarczone do klienta</FONT></P>
			<P STYLE="margin-bottom: 0.28cm; widows: 2; orphans: 2"><FONT SIZE=2 STYLE="font-size: 11pt">cancelled
			&ndash; anulowane</FONT></P>
			<P STYLE="widows: 2; orphans: 2"><FONT SIZE=1 STYLE="font-size: 8pt"><FONT COLOR="#000000"><FONT SIZE=2 STYLE="font-size: 11pt"><SPAN LANG="pl-PL">returned
			&ndash; zwrócone</SPAN></FONT></FONT></FONT></P>
		</TD>
	</TR>
	<TR VALIGN=TOP>
		<TD WIDTH=274>
			<P>order_total</P>
		</TD>
		<TD WIDTH=314>
			<P>Kwota całkowita zamówienia (brutto, z kosztami transportu)</P>
		</TD>
	</TR>
	<TR VALIGN=TOP>
		<TD WIDTH=274>
			<P>payment_method</P>
		</TD>
		<TD WIDTH=314>
			<P STYLE="margin-bottom: 0.28cm">Forma płatności za zamówienie.
			Zwracana jest jedna z 3 wartości:</P>
			<P STYLE="margin-bottom: 0.28cm">cash_on_delivery &ndash; za
			pobraniem</P>
			<P STYLE="margin-bottom: 0.28cm">bank_transfer &ndash; zwykły
			przelew bankowy</P>
			<P STYLE="margin-bottom: 0.28cm">online_bank_transfer &ndash;
			szybki przelew bankowy, wykonany za pośrednictwem operatora
			płatności</P>
			<P>credit_card &ndash; karta kredytowa/płatnicza, płatność
			wykonana za pośrednictwem operatora płatności</P>
		</TD>
	</TR>
	<TR VALIGN=TOP>
		<TD WIDTH=274>
			<P>order_due_amount</P>
		</TD>
		<TD WIDTH=314>
			<P>Kwota pozostała do zapłaty (zero dla zamówień opłaconych,
			wartość do pobrania dla płatności COD, inne wartości w przypadku
			modyfikacji zamówień po zapłacie lub w przypadku pomyłki Klienta)</P>
		</TD>
	</TR>
	<TR VALIGN=TOP>
		<TD WIDTH=274>
			<P>delivery_method</P>
		</TD>
		<TD WIDTH=314>
			<P STYLE="margin-bottom: 0.28cm">Metoda dostawy, możliwe są
			wartości:</P>
			<P STYLE="margin-bottom: 0.28cm">standard_courier</P>
			<P>inpost_parcel_locker</P>
		</TD>
	</TR>
	<TR VALIGN=TOP>
		<TD WIDTH=274>
			<P>shipment_tracking_number</P>
		</TD>
		<TD WIDTH=314>
			<P>Numer listu przewozowego</P>
		</TD>
	</TR>
	<TR VALIGN=TOP>
		<TD WIDTH=274>
			<P>pos_id</P>
		</TD>
		<TD WIDTH=314>
			<P>Identyfikator POS, jest to wartość pola identyfikator
			zewnętrzny, wpisany w parametry POS w panelu sprzedawcy. Służy do
			identyfikowania magazynu w przypadku realizacji zamówień w wielu
			magazynach lub sklepach.</P>
		</TD>
	</TR>
	<TR VALIGN=TOP>
		<TD WIDTH=274>
			<P>order_currency</P>
		</TD>
		<TD WIDTH=314>
			<P>Waluta w zamówieniu</P>
		</TD>
	</TR>
	<TR VALIGN=TOP>
		<TD WIDTH=274>
			<P>invoice_data</P>
		</TD>
		<TD WIDTH=314>
			<P><BR>
			</P>
		</TD>
	</TR>
	<TR VALIGN=TOP>
		<TD WIDTH=274>
			<P STYLE="margin-left: 1.25cm">invoice_required</P>
		</TD>
		<TD WIDTH=314>
			<P>Wymagana faktura &ndash; 1 &ndash; tak, 0 &ndash; nie. W
			przypadku gdy faktura jest wymagana, wypełniona jest struktura
			invoice_address. Jeżeli faktura nie jest wymagana to oznacza, że
			wymagany jest paragon.</P>
		</TD>
	</TR>
	<TR VALIGN=TOP>
		<TD WIDTH=274>
			<P STYLE="margin-left: 1.25cm">invoice_address</P>
		</TD>
		<TD WIDTH=314>
			<P><BR>
			</P>
		</TD>
	</TR>
	<TR VALIGN=TOP>
		<TD WIDTH=274>
			<P STYLE="margin-left: 2.5cm; margin-bottom: 0.28cm">invoice_first_name</P>
			<P STYLE="margin-left: 2.5cm; margin-bottom: 0.28cm">invoice_last_name</P>
			<P STYLE="margin-left: 2.5cm; margin-bottom: 0.28cm">invoice_company_name</P>
			<P STYLE="margin-left: 2.5cm; margin-bottom: 0.28cm">invoice_street</P>
			<P STYLE="margin-left: 2.5cm; margin-bottom: 0.28cm">invoice_city</P>
			<P STYLE="margin-left: 2.5cm; margin-bottom: 0.28cm">invoice_zip_code</P>
			<P STYLE="margin-left: 2.5cm; margin-bottom: 0.28cm">invoice_country</P>
			<P STYLE="margin-left: 2.5cm">invoice_tax_id</P>
		</TD>
		<TD WIDTH=314>
			<P STYLE="margin-bottom: 0.28cm"><BR><BR>
			</P>
			<P STYLE="margin-bottom: 0.28cm"><BR><BR>
			</P>
			<P STYLE="margin-bottom: 0.28cm"><BR><BR>
			</P>
			<P STYLE="margin-bottom: 0.28cm"><BR><BR>
			</P>
			<P STYLE="margin-bottom: 0.28cm"><BR><BR>
			</P>
			<P STYLE="margin-bottom: 0.28cm"><BR><BR>
			</P>
			<P STYLE="margin-bottom: 0.28cm"><BR><BR>
			</P>
			<P>NIP</P>
		</TD>
	</TR>
	<TR VALIGN=TOP>
		<TD WIDTH=274>
			<P>delivery_data</P>
		</TD>
		<TD WIDTH=314>
			<P>Adres dostawy &ndash; w tym dane do paczkomatów</P>
		</TD>
	</TR>
	<TR VALIGN=TOP>
		<TD WIDTH=274>
			<P STYLE="margin-left: 1.25cm">inpost_locker_id</P>
		</TD>
		<TD WIDTH=314>
			<P>Kod paczkomatu Inpost.</P>
		</TD>
	</TR>
	<TR VALIGN=TOP>
		<TD WIDTH=274>
			<P STYLE="margin-left: 1.25cm">delivery_address</P>
		</TD>
		<TD WIDTH=314>
			<P><BR>
			</P>
		</TD>
	</TR>
	<TR VALIGN=TOP>
		<TD WIDTH=274>
			<P STYLE="margin-left: 2.5cm; margin-bottom: 0.28cm">delivery_first_name</P>
			<P STYLE="margin-left: 2.5cm; margin-bottom: 0.28cm">delivery_last_name</P>
			<P STYLE="margin-left: 2.5cm; margin-bottom: 0.28cm">delivery_company_name</P>
			<P STYLE="margin-left: 2.5cm; margin-bottom: 0.28cm">delivery_street</P>
			<P STYLE="margin-left: 2.5cm; margin-bottom: 0.28cm">delivery_city</P>
			<P STYLE="margin-left: 2.5cm; margin-bottom: 0.28cm">delivery_zip_code</P>
			<P STYLE="margin-left: 2.5cm; margin-bottom: 0.28cm">delivery_country</P>
			<P STYLE="margin-left: 2.5cm">phone</P>
		</TD>
		<TD WIDTH=314>
			<P STYLE="margin-bottom: 0.28cm">Nazwa kupującego</P>
			<P STYLE="margin-bottom: 0.28cm"><BR><BR>
			</P>
			<P STYLE="margin-bottom: 0.28cm"><BR><BR>
			</P>
			<P><BR>
			</P>
		</TD>
	</TR>
	<TR VALIGN=TOP>
		<TD WIDTH=274>
			<P>order_items</P>
		</TD>
		<TD WIDTH=314>
			<P>Produkty zamówienia</P>
		</TD>
	</TR>
	<TR VALIGN=TOP>
		<TD WIDTH=274>
			<P STYLE="margin-left: 1.25cm">item</P>
		</TD>
		<TD WIDTH=314>
			<P><BR>
			</P>
		</TD>
	</TR>
	<TR VALIGN=TOP>
		<TD WIDTH=274>
			<P STYLE="margin-left: 2.5cm; margin-bottom: 0.28cm">is_delivery_item</P>
			<P STYLE="margin-left: 2.5cm"><BR>
			</P>
		</TD>
		<TD WIDTH=314>
			<P>Ta pozycja odpowiada za koszt dostawy &ndash; 1 &ndash; tak, 0
			&ndash; nie. 
			</P>
		</TD>
	</TR>
	<TR VALIGN=TOP>
		<TD WIDTH=274>
			<P STYLE="margin-left: 2.5cm">item_sku</P>
		</TD>
		<TD WIDTH=314>
			<P>Kod produktu w systemie partnera. Puste dla pozycji 
			zawierającej koszt dostawy.</P>
		</TD>
	</TR>
	<TR VALIGN=TOP>
		<TD WIDTH=274>
			<P STYLE="margin-left: 2.5cm">item_name</P>
		</TD>
		<TD WIDTH=314>
			<P>Nazwa produktu. 
			</P>
		</TD>
	</TR>
	<TR VALIGN=TOP>
		<TD WIDTH=274>
			<P STYLE="margin-left: 2.5cm">item_qty</P>
		</TD>
		<TD WIDTH=314>
			<P>Ilość produktu w zamówieniu. Dla kosztu dostawy zawsze 1.</P>
		</TD>
	</TR>
	<TR VALIGN=TOP>
		<TD WIDTH=274>
			<P STYLE="margin-left: 2.5cm">item_value_before_discount</P>
		</TD>
		<TD WIDTH=314>
			<P>Wartość łączna pozycji zamówienia przed rabatem (brutto, z
			uwzględnionym podatkiem VAT).</P>
		</TD>
	</TR>
	<TR VALIGN=TOP>
		<TD WIDTH=274>
			<P STYLE="margin-left: 2.5cm">item_discount</P>
		</TD>
		<TD WIDTH=314>
			<P>Wartość łączna rabatu dla pozycji zamówienia.</P>
		</TD>
	</TR>
	<TR VALIGN=TOP>
		<TD WIDTH=274>
			<P STYLE="margin-left: 2.5cm">item_value_after_discount</P>
		</TD>
		<TD WIDTH=314>
			<P>Wartość końcowa pozycji zamówienia (brutto, z uwzględnionym
			podatkiem VAT).</P>
		</TD>
	</TR>
</TABLE>
<P CLASS="western" STYLE="margin-left: 1.25cm; margin-bottom: 0.28cm">
<BR><BR>
</P>
<H2 CLASS="western" STYLE="font-style: normal"><A NAME="__RefHeading___Toc409469999"></A>
Przekazanie informacji o spakowaniu przesyłki</H2>
<P CLASS="western" STYLE="margin-left: 1.06cm; text-indent: -0.4cm; margin-bottom: 0.28cm">
<FONT SIZE=3>Funkcja: </FONT><FONT FACE="Courier 10 Pitch"><FONT SIZE=3>setOrderAsCollected</FONT></FONT><FONT SIZE=3>
</FONT>
</P>
<P CLASS="western" STYLE="margin-left: 1.06cm; text-indent: -0.4cm; margin-bottom: 0.28cm">
<FONT SIZE=3>	parametry:</FONT></P>
<P CLASS="western" STYLE="margin-left: 1.06cm; text-indent: -0.4cm; margin-bottom: 0.28cm">
<FONT SIZE=3>			</FONT><FONT FACE="Courier 10 Pitch"><FONT SIZE=3>string
sessionToken</FONT></FONT></P>
<P CLASS="western" STYLE="margin-left: 1.06cm; text-indent: -0.4cm; margin-bottom: 0.28cm">
<FONT FACE="Courier 10 Pitch"><FONT SIZE=3>			string[] orderID</FONT></FONT></P>
<P CLASS="western" STYLE="margin-left: 1.25cm; margin-bottom: 0.28cm">
Ustawia status zamówień w Galerii Handlowej na &bdquo;spakowane&rdquo;.
Należy wywołać tę metodę w momencie, gdy przesyłka jest spakowania i
gotowa do wysyłki. Klient nie jest notyfikowany ale w szczegółach
swojego zamówienia będzie widział, że przesyłka jest spakowana i
zostanie wkrótce wysłana. Ustawienie tego statusu odpowiednio
wcześnie zmniejsza ilość zapytań klientów o termin wysyłki produktów.</P>
<P CLASS="western" STYLE="margin-left: 1.25cm; margin-bottom: 0.28cm">
Po wywołaniu tej metody status pobierany metodą <I>getOrdersByID</I>
nie zmienia się. 
</P>
<P CLASS="western" STYLE="margin-left: 1.25cm; margin-bottom: 0.28cm"><A NAME="soap_response"></A>
<SPAN STYLE="font-style: normal">W przypadku gdy metoda zostanie
wywołana dla zamówienia w statusie innym niż &bdquo;oczekuje na
spakowanie&rdquo; otrzymamy błąd </SPAN><FONT FACE="Calibri, sans-serif"><SPAN STYLE="font-style: normal">&bdquo;</SPAN></FONT><CODE CLASS="western"><FONT FACE="Calibri, sans-serif">error_order_invalid_status</FONT></CODE><CODE CLASS="western"><FONT FACE="Calibri, sans-serif"><SPAN STYLE="font-style: normal">&rdquo;</SPAN></FONT></CODE></P>
<H2 CLASS="western" STYLE="font-style: normal"><A NAME="__RefHeading___Toc409470000"></A>
Przekazanie danych o wysyłce</H2>
<P CLASS="western" STYLE="margin-left: 1.06cm; text-indent: -0.4cm; margin-bottom: 0.28cm">
<FONT SIZE=3>Funkcja: </FONT><FONT FACE="Courier 10 Pitch"><FONT SIZE=3>setOrderShipment</FONT></FONT><FONT SIZE=3>
</FONT>
</P>
<P CLASS="western" STYLE="margin-left: 1.06cm; text-indent: -0.4cm; margin-bottom: 0.28cm">
<FONT SIZE=3>	parametry:</FONT></P>
<P CLASS="western" STYLE="margin-left: 1.06cm; text-indent: -0.4cm; margin-bottom: 0.28cm">
<FONT SIZE=3>			</FONT><FONT FACE="Courier 10 Pitch"><FONT SIZE=3>string
sessionToken</FONT></FONT></P>
<P CLASS="western" STYLE="margin-left: 1.06cm; text-indent: -0.4cm; margin-bottom: 0.28cm">
<FONT FACE="Courier 10 Pitch"><FONT SIZE=3>			string orderID, </FONT></FONT>
</P>
<P CLASS="western" STYLE="margin-left: 1.06cm; text-indent: -0.4cm; margin-bottom: 0.28cm">
<FONT FACE="Courier 10 Pitch"><FONT SIZE=3>			datetime dateShipped, </FONT></FONT>
</P>
<P CLASS="western" STYLE="margin-left: 1.06cm; text-indent: -0.4cm; margin-bottom: 0.28cm">
<FONT FACE="Courier 10 Pitch"><FONT SIZE=3>			string courier, </FONT></FONT>
</P>
<P CLASS="western" STYLE="margin-left: 1.06cm; text-indent: -0.4cm; margin-bottom: 0.28cm">
<FONT FACE="Courier 10 Pitch"><FONT SIZE=3>			string
shipmentTrackingNumber</FONT></FONT></P>
<P CLASS="western" STYLE="margin-left: 1.25cm; margin-bottom: 0.28cm">
Ustawia status zamówienia w Galerii Handlowej na &bdquo;shipped&rdquo;,
ustawia datę wysyłki, kuriera (aktualnie dozwolone UPS i DHL; w
przypadku wykorzystywania innego kuriera prosimy o informację a
zakres zostanie rozszerzony) oraz numer listu przewozowego do
śledzenia przesyłki. Do klienta zostaje wysłany mail z informacją o
wysyłce z numerem listu przewozowego i linkiem do śledzenia
przesyłki.</P>
<P CLASS="western" STYLE="margin-left: 1.25cm; margin-bottom: 0.28cm">
Metoda powinna być wywołana w momencie, gdy przesyłka jest przejęta
przez kuriera. 
</P>
<P CLASS="western" STYLE="margin-left: 1.25cm; margin-bottom: 0.28cm"><A NAME="soap_response1"></A>
<SPAN STYLE="font-style: normal">Metoda wymaga podania daty wysyłki w
formacie &bdquo;YYYY-mm-dd HH:ii:ss&rdquo; <BR>(np. 2015-03-31
11:30:55), w przeciwnym razie zwróci błąd
&bdquo;error_wrong_datetime_format&rdquo;. Podobnie jak poprzednia w
przypadku wywołania na zamówieniu o statusie różnym od &bdquo;oczekuje
na spakowanie&rdquo; zwraca błąd </SPAN><FONT FACE="Calibri, sans-serif"><SPAN STYLE="font-style: normal">&bdquo;</SPAN></FONT><CODE CLASS="western"><FONT FACE="Calibri, sans-serif"><SPAN STYLE="font-style: normal">error_order_invalid_status</SPAN></FONT></CODE><CODE CLASS="western"><FONT FACE="Calibri, sans-serif"><SPAN STYLE="font-style: normal">&rdquo;</SPAN></FONT></CODE></P>
<H2 CLASS="western" STYLE="margin-left: 1.02cm; text-indent: -1.02cm; font-style: normal; page-break-inside: avoid"><A NAME="__RefHeading___Toc409470001"></A>
Przekazanie danych o rezerwacji</H2>
<P CLASS="western" STYLE="margin-left: 1.06cm; text-indent: -0.4cm; margin-bottom: 0.28cm; page-break-inside: avoid; page-break-after: avoid">
<FONT SIZE=3>Funkcja: </FONT><FONT FACE="Courier 10 Pitch"><FONT SIZE=3>setOrderReservation</FONT></FONT><FONT SIZE=3>
</FONT>
</P>
<P CLASS="western" STYLE="margin-left: 1.06cm; text-indent: -0.4cm; margin-bottom: 0.28cm">
<FONT SIZE=3>	 parametry:</FONT></P>
<P CLASS="western" STYLE="margin-left: 1.06cm; text-indent: -0.4cm; margin-bottom: 0.28cm">
<FONT SIZE=3>			</FONT><FONT FACE="Courier 10 Pitch"><FONT SIZE=3>string
sessionToken</FONT></FONT></P>
<P CLASS="western" STYLE="margin-left: 1.06cm; text-indent: -0.4cm; margin-bottom: 0.28cm">
<FONT FACE="Courier 10 Pitch"><FONT SIZE=3>			string orderID, </FONT></FONT>
</P>
<P CLASS="western" STYLE="margin-left: 1.06cm; text-indent: -0.4cm; margin-bottom: 0.28cm">
<FONT FACE="Courier 10 Pitch"><FONT SIZE=3>			string
reservationStatus, </FONT></FONT>
</P>
<P CLASS="western" STYLE="margin-left: 1.06cm; text-indent: -0.4cm; margin-bottom: 0.28cm">
<FONT FACE="Courier 10 Pitch"><FONT SIZE=3>			string
reservationMessage</FONT></FONT></P>
<P CLASS="western" STYLE="margin-left: 1.25cm; margin-bottom: 0.28cm">
Metoda ustawia w Galerii Handlowej informację o statusie rezerwacji
towarów do zamówienia. Dzięki temu operator obsługujący zamówienia
będzie wiedział o braku rezerwacji i będzie mógł odpowiednio
zareagować i obsłużyć taką sytuację. 
</P>
<P CLASS="western" STYLE="margin-left: 1.25cm; margin-bottom: 0.28cm">
<I>reservationStatus </I>&ndash; akceptowane są wartość &bdquo;ok&rdquo;
dla pomyślnie dokonanej rezerwacji, &bdquo;problem&rdquo; w sytuacji,
gdy brak jest rezerwacji dla całego zamówienia.</P>
<P CLASS="western" STYLE="margin-left: 1.25cm; margin-bottom: 0.28cm">
W przypadku otrzymanie negatywnego wyniku rezerwacji  po stronie
Galerii Handlowej w zamówieniu zapisywane są następujące informacje:</P>
<P CLASS="western" STYLE="margin-left: 1.25cm; margin-bottom: 0.28cm">
- komentarzach do zamówienia zapisuje się informacja o negatywnym
wyniku rezerwacji wraz z treścią komunikatu rezerwacji
(<I>reservationMessage</I>),</P>
<P CLASS="western" STYLE="margin-left: 1.25cm; margin-bottom: 0.28cm">
- zostaje ustawiony alert &bdquo;Brak rezerwacji do zamówienia w
systemie sprzedawcy&rdquo;,</P>
<P CLASS="western" STYLE="margin-left: 1.25cm; margin-bottom: 0.28cm">
- status zamówienia jest przestawiany na &bdquo;problem&rdquo;
(metodą <I>getOrdersByID</I> zostanie zwrócony status pending).</P>
<P CLASS="western" STYLE="margin-bottom: 0.28cm">W przypadku
otrzymanie pozytywnego wyniku rezerwacji  po stronie Galerii
Handlowej w komentarzach do zamówienia zapisuje się informacja o
pozytywnym wyniku rezerwacji wraz z treścią komunikatu rezerwacji
(<I>reservationMessage</I>). Jeśli wcześniej w zamówieniu był
ustawiony alert braku rezerwacji jest on kasowany.</P>
<H1 CLASS="western" STYLE="page-break-before: always"><A NAME="__RefHeading___Toc409470002"></A>
Konfiguracja API</H1>
<P CLASS="western" STYLE="margin-left: 0.64cm; margin-bottom: 0.28cm">
API można skonfigurować w panelu sprzedawcy systemu Galerii Handlowej
w opcji Ustawienia &rarr; Konfiguracja API.</P>
<P CLASS="western" STYLE="margin-left: 0.64cm; margin-bottom: 0.28cm">
Korzystaj z API do pobierania zamówień</P>
<P CLASS="western" STYLE="margin-left: 2.5cm; margin-bottom: 0.28cm">tak/nie</P>
<P CLASS="western" STYLE="margin-left: 1.25cm; margin-bottom: 0.28cm">
To ustawienie jest konfigurowane przez zespół Galerii Handlowej.
Poinformuj nas, że chcesz otrzymać dostęp do API.</P>
<H2 CLASS="western"><A NAME="__RefHeading___Toc409470003"></A>Konfiguracja
techniczna</H2>
<P CLASS="western" STYLE="margin-left: 1.25cm; margin-bottom: 0.28cm">
Dane do logowania do API</P>
<P CLASS="western" STYLE="margin-left: 1.25cm; margin-bottom: 0.28cm">
Identyfikator partnera (vendorId)</P>
<P CLASS="western" STYLE="margin-left: 3.75cm; margin-bottom: 0.28cm">
&lt;id partnera systemowe&gt;</P>
<P CLASS="western" STYLE="margin-left: 1.25cm; margin-bottom: 0.28cm">
Hasło do API (password)</P>
<P CLASS="western" STYLE="margin-left: 3.75cm; margin-bottom: 0.28cm">
&lt;pole do wprowadzenia hasła&gt;</P>
<P CLASS="western" STYLE="margin-left: 1.25cm; margin-bottom: 0.28cm">
Klucz API (webAPIKey)</P>
<P CLASS="western" STYLE="margin-left: 3.75cm; margin-bottom: 0.28cm">
&lt;długi i generowany automatycznie&gt;</P>
<H2 CLASS="western"><A NAME="__RefHeading___Toc409470004"></A>Konfiguracja
biznesowa</H2>
<P CLASS="western" STYLE="margin-left: 1.25cm; margin-bottom: 0.28cm">
Po pobraniu zamówienia przez API przestań rezerwować zapas w systemie
galerii 
</P>
<P CLASS="western" STYLE="margin-left: 1.25cm; margin-bottom: 0.28cm">
		tak/nie.</P>
<P CLASS="western" STYLE="margin-left: 2.5cm; margin-bottom: 0.28cm">Standardowo
zamówienia niezrealizowane ciągle rezerwują zapas w systemie Galerii
Handlowej. Oznacza to, że  przy kolejnym przetworzenie pliku  z
dostępnościami produktów ilości z niezrealizowanych zamówień będą
pomniejszały zapas przekazany w pliku. 
</P>
<P CLASS="western" STYLE="margin-left: 2.5cm; margin-bottom: 0.28cm">Zaznacz
&bdquo;tak&rdquo; jeśli w Twoim systemie magazynowym tworzysz
rezerwacje (pomniejszasz dostępny zapas) od razu po zaimportowaniu
zamówienia z Galerii Handlowej i w następnym pliku z zapasem
wysyłanym do Galerii Handlowej sam pomniejszasz zapas o te
zamówienia. 
</P>
<P CLASS="western" STYLE="margin-left: 2.5cm; margin-bottom: 0.28cm">Zaznacz
&bdquo;nie&rdquo;, jeśli w Twoim systemie magazynowym nie tworzysz
rezerwacji lub tworzysz je później i poinformujesz nas o tym
wywołując metodę <I>setOrderReservation</I> dla zamówienia. W tym
przypadku przestaniemy rezerwować zapas dla zamówienia po wysłaniu
zamówienia do klienta (po użyciu metody <I>setOrderShipment</I>) lub
po wywołaniu metody <I>setOrderReservation.</I></P>
<P CLASS="western" STYLE="margin-left: 1.25cm; margin-bottom: 0.28cm; page-break-after: avoid">
Informuj o zmianach w zamówieniu dotyczących:</P>
<DL>
	<DD>
	<TABLE WIDTH=620 BORDER=0 CELLPADDING=4 CELLSPACING=0>
		<COL WIDTH=78>
		<COL WIDTH=527>
		<TR VALIGN=TOP>
			<TD WIDTH=78>
				<P CLASS="western" STYLE="margin-left: 0.28cm; margin-right: 0.1cm; page-break-after: avoid">
				tak/nie</P>
			</TD>
			<TD WIDTH=527>
				<P CLASS="western" STYLE="page-break-after: avoid">Pojawiło się
				nowe zamówienie do realizacji.</P>
			</TD>
		</TR>
		<TR VALIGN=TOP>
			<TD WIDTH=78>
				<P CLASS="western" STYLE="margin-left: 0.28cm; margin-right: 0.1cm; page-break-after: avoid">
				tak/nie</P>
			</TD>
			<TD WIDTH=527>
				<P CLASS="western" STYLE="page-break-after: avoid">Zamówienie
				zostało anulowane.</P>
			</TD>
		</TR>
		<TR VALIGN=TOP>
			<TD WIDTH=78>
				<P CLASS="western" STYLE="margin-left: 0.28cm; margin-right: 0.1cm; page-break-after: avoid">
				tak/nie</P>
			</TD>
			<TD WIDTH=527>
				<P CLASS="western" STYLE="page-break-after: avoid">Zmieniła się
				metoda płatności w zamówienia lub/i zmieniła się się płatność do
				zamówienia.</P>
			</TD>
		</TR>
		<TR VALIGN=TOP>
			<TD WIDTH=78>
				<P CLASS="western" STYLE="margin-left: 0.28cm; margin-right: 0.1cm; page-break-after: avoid">
				tak/nie</P>
			</TD>
			<TD WIDTH=527>
				<P CLASS="western" STYLE="page-break-after: avoid">Zmieniły się
				produkty lub koszt dostawy w zamówieniu. Produkty mogły być
				usunięte, dodane, zmieniona cena, ilość, rabat. 
				</P>
			</TD>
		</TR>
		<TR VALIGN=TOP>
			<TD WIDTH=78>
				<P CLASS="western" STYLE="margin-left: 0.28cm; margin-right: 0.1cm; page-break-after: avoid">
				tak/nie</P>
			</TD>
			<TD WIDTH=527>
				<P CLASS="western" STYLE="page-break-after: avoid">Zmieniła się
				metoda dostawy lub/i adres dostawy. 
				</P>
			</TD>
		</TR>
		<TR VALIGN=TOP>
			<TD WIDTH=78>
				<P CLASS="western" STYLE="margin-left: 0.28cm; margin-right: 0.1cm; page-break-after: avoid">
				tak/nie</P>
			</TD>
			<TD WIDTH=527>
				<P CLASS="western" STYLE="page-break-after: avoid">Zmieniły się
				dane do faktury lub/i informacja czy faktura jest wymagana.</P>
			</TD>
		</TR>
		<TR VALIGN=TOP>
			<TD WIDTH=78>
				<P CLASS="western" STYLE="margin-left: 0.28cm; margin-right: 0.1cm; page-break-after: avoid">
				tak/nie</P>
			</TD>
			<TD WIDTH=527>
				<P CLASS="western" STYLE="page-break-after: avoid">Zmienił się
				status zamówienia. 
				</P>
			</TD>
		</TR>
	</TABLE>
</DL>
<P CLASS="western" STYLE="margin-left: 1.25cm; margin-bottom: 0.28cm; page-break-after: avoid">
Jeśli dla jakiegoś typu zdarzeń wyłączysz informowanie nie będziesz
otrzymywał komunikatów tego typu metodą  <I>getChangeOrderMessage.</I>
</P>
<P CLASS="western" STYLE="margin-bottom: 0.28cm"><BR><BR>
</P>
<H2 CLASS="western" STYLE="font-style: normal"><A NAME="_Ref409469970"></A><A NAME="__RefHeading___Toc409470005"></A>
Rezerwacje a dostępność produktów partnera w Galerii Handlowej</H2>
<P CLASS="western" STYLE="margin-bottom: 0.28cm">Dostępność produktów
partnera w Galerii Handlowej jest określana na podstawie plików z
dostępnościami produktów. Pliki z dostępnościami mogą dotyczyć wielu
punktów sprzedaży lub magazynów. Zakładając, że w systemie nie ma
złożonych zamówień na produkt to łączna ilość produktów jest
wyjściowym zapasem dostępnym w systemie Galerii Handlowej.</P>
<P CLASS="western" STYLE="margin-bottom: 0.28cm">Po złożeniu
zamówienia przez klienta ilość ta jest dekrementowana. Jeśli zapas
wyczerpie się produkt traci dostępność. Przestaje być prezentowany na
listingach, po wejściu na kartę produktu nie można go dodać do
koszyka. Złożenie zamówienia na produkt przestaje być możliwe. 
</P>
<P CLASS="western" STYLE="margin-bottom: 0.28cm">Załadowanie pliku z
dostępnościami powoduje wyliczenie zapasu dostępnego ze wszystkich
POS/magazynów i sprawdzenie czy w systemie nie ma złożonych zamówień
na produkt, które powinny rezerwować (pomniejszać) zapas w systemie
Galerii Handlowej. Jeśli partner nie korzysta z API są to wyłącznie
zamówienia niewysłane do klienta.</P>
<P CLASS="western" STYLE="margin-bottom: 0.28cm">Jeśli partner
korzysta z API to o pomniejszeniu zapasu decyduje znacznik
rezerwacji. Każde nowe zamówienie złożone przez klienta nie ma tego
znacznika, ustawić go może jedno ze zdarzeń:</P>
<P CLASS="western" STYLE="margin-left: 1.25cm; margin-bottom: 0.28cm">
- potwierdzenie odebrania komunikatu o nowym zamówieniu jeśli
parametr w konfiguracji API dla partnera &bdquo;Po pobraniu
zamówienia przez API przestań rezerwować zapas w systemie galerii&rdquo;
jest ustawiony na &bdquo;tak&rdquo;, 
</P>
<P CLASS="western" STYLE="margin-left: 1.25cm; margin-bottom: 0.28cm">
- informacja o utworzeniu rezerwacji dla zamówienia (metoda
<I>setOrderReservation</I>),</P>
<P CLASS="western" STYLE="margin-left: 1.25cm; margin-bottom: 0.28cm">
- otrzymanie informacji o wykonanej wysyłce zamówienia (metoda
<I>setOrderShipment</I>).</P>
<P CLASS="western" STYLE="margin-bottom: 0.28cm">Wynika z tego, że
najpóźniejszym momentem gdy należy pomniejszyć zapas w przesyłanym do
Galerii Handlowej pliku z dostępnościami jest moment poinformowania
Galerii o wysyłce zamówienia. Jeśli rezerwacja do zamówienia w
systemie partnera jest tworzona wcześniej to można o tym informować
Galerię Handlową na dwa sposoby.</P>
<P CLASS="western" STYLE="margin-bottom: 0.28cm">Należy zauważyć, że
uwzględnienie zamówienia w kalkulacji zapasu dostępnego w Galerii
Handlowej a informacja o rezerwacji lub braku rezerwacji do
zamówienia to dwie różne rzeczy. 
</P>
<P CLASS="western" STYLE="margin-bottom: 0.28cm">Pierwsza to tylko
kalkulacja bezpiecznego zapasu, to znaczy takiego żeby wyeliminować
składanie zamówień przez klientów na zarezerwowany innymi
zamówieniami towar. 
</P>
<P CLASS="western" STYLE="margin-bottom: 0.28cm">Informacja o
rezerwacji w zamówieniu pochodzić może tylko z metody
<I>setOrderReservation</I> i wspomaga obsługujących zamówienia. 
</P>
<P CLASS="western" STYLE="margin-bottom: 0.28cm"><BR><BR>
</P>
<H1 CLASS="western"><A NAME="__RefHeading___Toc409470006"></A>Wsparcie
techniczne</H1>
<P CLASS="western" STYLE="margin-bottom: 0.28cm">Bieżącą pomoc w
procesie integracji z Galerią Modago można uzyskać kontaktując się z:</P>
<P CLASS="western" STYLE="margin-bottom: 0.28cm">email:
<FONT COLOR="#0563c1"><U><A HREF="mailto:integracja@zolago.com">integracja@zolago.com</A></U></FONT></P>
<P CLASS="western" STYLE="margin-bottom: 0.28cm">tel: 604&nbsp;291&nbsp;081
</P>
<P CLASS="western" STYLE="margin-bottom: 0.28cm; line-height: 105%; widows: 2; orphans: 2">
<BR><BR>
</P>
<DIV TYPE=FOOTER>
	<P CLASS="western" STYLE="margin-top: 1.13cm; margin-bottom: 0.28cm; line-height: 105%; widows: 2; orphans: 2">
	<BR><BR>
	</P>
</DIV>

EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "Panel vendora - Pomoc - Ustawienia - Użytkownicy",
	"identifier" => "udropship-help-pl-udropship-operator",
  "content" => 
<<<EOT
<h4>UŻYTKOWNICY</h4>
Jeśli posiadasz konto administracyjne, masz uprawnienia pozwalające Ci zarządzać użytkownikami konta firmowego. Możesz więc edytować dane użytkowników, zmieniać ich role (uprawnienia) oraz dodawać nowych. 

 <br/> <br/>
<h5>DODAWANIE UŻYTKOWNIKÓW</h5>
<p>Aby dodać nowego użytkownika, kliknij w przycisk „Dodaj użytkownika” i wypełnij jego dane. Podaj jego adres e-mail i stwórz dla niego hasło tymczasowe – użytkownik będzie je mógł zmienić po pierwszym zalogowaniu. 

<p>Aby użytkownik miał dostęp do konta, musisz zmienić status „Aktywny” na „Tak”.
Podaj imię i nazwisko użytkownika oraz telefon kontaktowy.
Określ jakie uprawnienia ma mieć użytkownik, przypisując mu role i widoczne dla niego punkty obsługi sprzedaży (Dozwolone POS). Jeśli chcesz zaznaczyć więcej niż jedną pozycję na liście, zaznacz kolejne przytrzymując przycisk CTRL. 
Poniżej lista możliwych ról:
<ul style="padding-left:20px">
<li>Obsługa zamówień – dla osób, które zajmują się obsługą i realizacją zamówień, osoba ta będzie widziała wszystkie zamówienia dla wybranych dla niej „Dozwolonych POS” i będzie mogła nimi zarządzać.</li>
<li>Zarządzanie kampaniami – dla osób zajmujących się prezentacją sklepu i oferty na stronie serwisu, merchandisingiem czy też marketingiem, osoba ta będzie miała dostęp do panelu zarządzania kampaniami, będzie mogła wprowadzać kreacje reklamowe i publikować je na stronie. </li>
<li>Obsługa RMA – dla osób zajmujących się obsługą zwrotów i reklamacji, osoba ta będzie widziała wszystkie szczegóły związane ze zgłoszeniami RMA i nimi zarządzać.</li>
<li>Obsługa helpdesk – dla osób zajmujących się obsługą klienta, osoba ta będzie miała dostęp do panelu z pytaniami klientów i będzie odpowiadała na bieżące zapytania. </li>
<li>Zarządzanie opisami i zdjęciami produktów – dla osób zajmujących się budowaniem oferty i prezentacją produktów, osoba ta będzie miała możliwość wprowadzania zdjęć i łączenia ich z produktami oraz wprowadzania i edycji opisów produktów.</li>
<li>Zarządzanie cenami produktów – dla osób odpowiedzialnych za sprzedaż, osoby te będą miały dostęp do panelu zarządzania cenami i będą mogły tworzyć reguły usprawniające zarządzanie cenami. </li>
<li>Zarządzanie płatnościami – dla osób zajmującymi się rozliczeniami finansowymi, osoby te będą miały dostęp do szczegółów rozliczeń i płatności.</li>
<li>Ustawienia GH API – dla osób technicznych, zajmujących się integracją i wymianą plików XML między systemami, osoba ta otrzyma dostęp do panelu API. </li></ul></p>
<br/>
<h5>EDYCJA DANYCH UŻYTKOWNIKÓW</h5>
<p>Jeśli chcesz sprawdzić szczegóły uprawnień użytkownika i/lub je zmienić, kliknij w "Edytuj" przy wybranej osobie. Przejdziesz do strony z ustawieniami dla użytkownika, gdzie możesz zmodyfikować jego dane kontaktowe oraz uprawnienia. </p>
EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "Panel vendora - Pomoc - Ustawienia - Użytkownicy - Dodawanie Użytkownika",
	"identifier" => "udropship-help-pl-udropship-operator-edit",
  "content" => 
<<<EOT
<h4>DODAWANIE UŻYTKOWNIKÓW</h4>
<p>Podaj adres e-mail użytkownika i stwórz dla niego hasło tymczasowe – użytkownik będzie je mógł zmienić po pierwszym zalogowaniu. </p>

<p>Aby użytkownik miał dostęp do konta, musisz zmienić status „Aktywny” na „Tak”.</p>
<p>Podaj imię i nazwisko użytkownika oraz telefon kontaktowy.</p>
<p>Określ jakie uprawnienia ma mieć użytkownik, przypisując mu role i widoczne dla niego punkty obsługi sprzedaży (Dozwolone POS). Jeśli chcesz zaznaczyć więcej niż jedną pozycję na liście, zaznacz kolejne przytrzymując przycisk CTRL. 
Poniżej lista możliwych ról:
<ul style="padding-left:20px">
<p><li><b>Obsługa zamówień </b>– dla osób, które zajmują się obsługą i realizacją zamówień, osoba ta będzie widziała wszystkie zamówienia dla wybranych dla niej „Dozwolonych POS” i będzie mogła nimi zarządzać.</li></p>
<p><li><b>Zarządzanie kampaniami</b> – dla osób zajmujących się prezentacją sklepu i oferty na stronie serwisu, merchandisingiem czy też marketingiem, osoba ta będzie miała dostęp do panelu zarządzania kampaniami, będzie mogła wprowadzać kreacje reklamowe i publikować je na stronie. </li></p>
<p><li><b>Obsługa RMA </b>– dla osób zajmujących się obsługą zwrotów i reklamacji, osoba ta będzie widziała wszystkie szczegóły związane ze zgłoszeniami RMA i nimi zarządzać.</li></p>
<p><li><b>Obsługa helpdesk </b>– dla osób zajmujących się obsługą klienta, osoba ta będzie miała dostęp do panelu z pytaniami klientów i będzie odpowiadała na bieżące zapytania. </li></p>
<p><li><b>Zarządzanie opisami i zdjęciami produktów </b>– dla osób zajmujących się budowaniem oferty i prezentacją produktów, osoba ta będzie miała możliwość wprowadzania zdjęć i łączenia ich z produktami oraz wprowadzania i edycji opisów produktów.</li></p>
<p><li><b>Zarządzanie cenami produktów </b>– dla osób odpowiedzialnych za sprzedaż, osoby te będą miały dostęp do panelu zarządzania cenami i będą mogły tworzyć reguły usprawniające zarządzanie cenami. </li></p>
<p><li><b>Zarządzanie płatnościami</b> – dla osób zajmującymi się rozliczeniami finansowymi, osoby te będą miały dostęp do szczegółów rozliczeń i płatności.</li></p>
<p><li><b>Ustawienia GH API </b>– dla osób technicznych, zajmujących się integracją i wymianą plików XML między systemami, osoba ta otrzyma dostęp do panelu API. </li></p></ul></p>
EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "Panel vendora - Pomoc - Ustawienia - POS - Lista POS",
	"identifier" => "udropship-help-pl-udropship-pos",
  "content" => 
<<<EOT
<h4>PUNKTY OBSŁUGI SPRZEDAŻY</h4>
W tym miejscu możesz zarządzać ustawieniami dla punktów obsługi sprzedaży (POS) – magazynów i sklepów, z których wysyłane są produkty. <br/><br/>

<h5>DODAWANIE NOWEGO PUNKTU OBSŁUGI SPRZEDAŻY</h5>
<p>Aby dodać nowy POS, kliknij w przycisk „Dodaj POS” i wypełnij jego dane. </p>

<p><b>Parametry POS:</b>
<ul style="padding-left:20px">
<li>Nazwa – podaj nazwę punktu sprzedaży, który będzie identyfikował go w systemie.</li>
<li>Aktywny – aby punkt sprzedaży był widoczny w panelu zarządzaniu zamówieniami i produktami, musi być mieć ustawiony status na „Tak”. Jeśli chcesz szybko wyłączyć POS, zmień status na nieaktywny.</li>
<li>Zewnętrzne ID – pole pozwala skorzystać z własnego ID punktu sprzedaży, nazwy z własnego, zewnętrznego systemu obsługującego sprzedaż, który pomoże identyfikować POS. </li>
</ul>
</p>

<p><b>Kontakt:</b></br>
Podaj numer telefonu bezpośrednio do punktu sprzedaży i adres e-mail do szybkiego kontaktu z tym POS.</p>

<p><b>Adres:</b></br>
Podaj dane adresowe POS. </p>

<p><b>Konfiguracja zapasu:</b></br>
Ustawienie to pozwala lepiej zarządzać stanami magazynowymi i zapewnić właściwą informację o dostępności produktów. 
<ul style="padding-left:25px">
<p><li>Minimalna ilość – określa wymaganą dostępność produktu w punkcie sprzedaży, niezbędną aby produkt był wyświetlany jako dostępny do kupienia, przykładowo: jeśli w polu minimalna ilość wpisana jest wartość 3, a punkcie sprzedaży stan magazynowy produktu spadnie do 2, produkt zmieni status na niedostępny.</li></p>
<p><li>Priorytet – określa, do którego POS ma być przypisane zamówienie, w przypadku takiego samego stanu magazynowego na zakupiony produkt. </li> 
</ul></p>

<p><b>Konfiguracja DHL i UPS:</b><br/>
Jeśli chcesz ustawić inne niż domyślne ustawienie spedytora dla danego POS, zmień ustawienie przy wybranym spedytorze „Wysyłaj przez….” Na „Tak” i wypełnij niezbędne pola. </p>

<br/>
<h5>EDYCJA USTAWIEŃ PUNKTU OBSŁUGI SPRZEDAŻY</h5>
<p>Jeśli chcesz sprawdzić szczegóły ustawień dla konkretnego POS lub/i je zmienić, kliknij w "Edytuj" przy wybranym punkcie sprzedaży. Przejdziesz do strony ze szczegółami dla danego punktu obsługi sprzedaży, gdzie możesz zmodyfikować poszczególne ustawienia. </p>

EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "Panel vendora - Pomoc - Ustawienia - POS - Dodawanie POS",
	"identifier" => "udropship-help-pl-udropship-pos-edit",
  "content" => 
<<<EOT
<h4>DODAWANIE NOWEGO PUNKTU OBSŁUGI SPRZEDAŻY</h4>
<p>Wypełnij poszczególne dane dla nowego punktu obsługi sprzedaży (POS).</p>

<br/>
<h5>PARAMETRY POS</h5>
<ul style="padding-left:20px">
<p><li><b>Nazwa </b>– podaj nazwę punktu sprzedaży, który będzie identyfikował go w systemie.</li></p>
<p><li><b>Aktywny </b>– aby punkt sprzedaży był widoczny w panelu zarządzaniu zamówieniami i produktami, musi być mieć ustawiony status na „Tak”. Jeśli chcesz szybko wyłączyć POS, zmień status na nieaktywny.</li></p>
<p><li><b>Zewnętrzne ID </b>– pole pozwala skorzystać z własnego ID punktu sprzedaży, nazwy z własnego, zewnętrznego systemu obsługującego sprzedaż, który pomoże identyfikować POS. </li></p>
</ul>
</p>

<br/>
<h5>KONTAKT</h5>
Podaj numer telefonu bezpośrednio do punktu sprzedaży i adres e-mail do szybkiego kontaktu z tym POS.

<br/><br/>
<h5>ADRES</h5>
Podaj dane adresowe POS. 

<br/><br/>
<h5>KONFIGURACJA ZAPASU</h5>
Ustawienie to pozwala lepiej zarządzać stanami magazynowymi i zapewnić właściwą informację o dostępności produktów. 
<ul style="padding-left:25px">
<p><li><b>Minimalna ilość</b> – określa wymaganą dostępność produktu w punkcie sprzedaży, niezbędną aby produkt był wyświetlany jako dostępny do kupienia, przykładowo: jeśli w polu minimalna ilość wpisana jest wartość 3, a punkcie sprzedaży stan magazynowy produktu spadnie do 2, produkt zmieni status na niedostępny.</li></p>
<p><li><b>Priorytet</b> – określa, do którego POS ma być przypisane zamówienie, w przypadku takiego samego stanu magazynowego na zakupiony produkt. </li> 
</ul></p>

<br/><br/>
<h5>KONFIGURACJA DHL i UPS:</h5>
Jeśli chcesz ustawić inne niż domyślne ustawienie spedytora dla danego POS, zmień ustawienie przy wybranym spedytorze „Wysyłaj przez….” Na „Tak” i wypełnij niezbędne pola. </p>
EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "Panel vendora - Pomoc - Ustawienia - Tabele rozmiarów",
	"identifier" => "udropship-help-pl-udropship-sizetable",
  "content" => 
<<<EOT
<h4>TABELE ROZMIARÓW</h4>
Właściwa informacja o rozmiarach produktów potrafi znacząco zmniejszyć ilość zwrotów związanych z zakupem za małego lub za dużego ubrania. Dlatego ważne jest by przy każdym produkcie była tabela rozmiarów mówiąca o tym jakie wymiary ma produkt i w jaki sposób zmierzyć swoje ciało, by wybrać właściwy dla siebie rozmiar. 

<br><br>
<h5>DODAWANIE TABELI ROZMIARÓW</h5>
<p>Ponieważ rozmiarówka bardzo się różni w zależności od kategorii produktowej i od producenta trzeba wprowadzić oddzielne tabele dla każdej kombinacji. </p>

<p>Aby dodać nową tabelę. Kliknij w przycisk „Dodaj nową tabelę rozmiarów”. </p>

<p>Nazwij tabelę rozmiarów tak, aby móc ją łatwo zidentyfikować i przypisać potem do właściwej kategorii produktowej czy producenta. </p>

<p>System umożliwia wprowadzenie tabel rozmiarów dla różnych wersji językowych strony, dopóki nie zostaną jednak uruchomione kolejne języki, wystarczy wypełnić pole „Domyślna tabela rozmiarów”. Jest to pole z edytorem tekstowym, który ułatwi wprowadzenie tabeli. </p>

<p>Używając edytora tekstowego możesz stworzyć własną tabelę lub wgrać i wprowadzić zdjęcie. Zdjęcia należy zawsze wgrywać za pomocą opcji „Wgraj obrazek” (ostatnia ikonka), dzięki temu będą one serwowane z naszej, bezpiecznej bazy. Nie należy wstawiać obrazków wskazując inne źródło – jeśli bowiem zawiedzie serwer zewnętrzny, tabela nie będzie się właściwie wyświetlać, a my nie mamy nad tym kontroli. </p>

<p style="background: white none repeat scroll 0% 0%; border: 1px solid #dddddd; padding: 15px;">Uwaga!<br>Pamiętaj, że strona Modago.pl jest responsywna &ndash; co oznacza, że zawartość ekranu dostosowuje się do jego rozmiaru. Strona nie może więc mieć w swojej treści elementów, które mają na sztywno określoną szerokość bezwzględną. Wszystkie wprowadzane elementy muszą się skalować razem ze stroną i mieścić nawet na ekranach telefonu. </p>
<br/>


<h5>PRZYPISYWANIE TABEL</h5>
<p>Gdy już wprowadzisz tabele rozmiarów, trzeba je jeszcze przypisać do właściwych produktów - wskazać, w których produktach ma się dana tabela wyświetlać.</p>

<p>Aby to zrobić:
<ul style="padding-left:20px">
<li>wybierz markę produktów, do których chcesz przypisać tabele rozmiarów z dostępnej listy rozwijanej</li>
<li>wybierz kategorię produktów danego producenta, do których chcesz przypisać tabelę rozmiarów z rozwijanej listy</li>
<li>wybierz z listy tabelę, którą chcesz przypisać</li>
<li>zatwierdź wybór klikając w przycisk "Zapisz"</li>
</ul></p>

<br/>
<h5>EDYCJA TABEL</h5>
<p>Z poziomu listy tabel możesz przejść w szczegóły tabeli i ja edytować lub usunąć</p><br/><br/>
EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "Panel vendora - Pomoc - Ustawienia - Tabele rozmiarów - Dodawanie tabel",
	"identifier" => "udropship-help-pl-udropship-sizetable-edit",
  "content" => 
<<<EOT
<h4>DODAWANIE I EDYCJA TABELI ROZMIARÓW</h4>
<p>Ponieważ rozmiarówka bardzo się różni w zależności od kategorii produktowej i od producenta trzeba wprowadzić oddzielne tabele dla każdej kombinacji. Pamiętaj o tym przy wprowadzaniu nowej tabeli rozmiarów.</p>
Każdą z tabel możesz potem edytować wchodząc w jej szczegóły w poziomu listy tabel.</p>

<p>Nazwij nową tabelę rozmiarów tak, aby móc ją łatwo zidentyfikować i przypisać potem do właściwej kategorii produktowej czy producenta. </p>

<p>System umożliwia wprowadzenie tabel rozmiarów dla różnych wersji językowych strony, dopóki nie zostaną jednak uruchomione kolejne języki, wystarczy wypełnić pole „Domyślna tabela rozmiarów”. Jest to pole z edytorem tekstowym, który ułatwi wprowadzenie tabeli. </p>

<p>Używając edytora tekstowego możesz stworzyć własną tabelę lub wgrać i wprowadzić zdjęcie. Zdjęcia należy zawsze wgrywać za pomocą opcji „Wgraj obrazek” (ostatnia ikonka), dzięki temu będą one serwowane z naszej, bezpiecznej bazy. Nie należy wstawiać obrazków wskazując inne źródło – jeśli bowiem zawiedzie serwer zewnętrzny, tabela nie będzie się właściwie wyświetlać, a my nie mamy nad tym kontroli. </p>
<br/>
<p style="background: white none repeat scroll 0% 0%; border: 1px solid #dddddd; padding: 15px;">Uwaga!<br>Pamiętaj, że strona Modago.pl jest responsywna &ndash; co oznacza, że zawartość ekranu dostosowuje się do jego rozmiaru. Strona nie może więc mieć w swojej treści elementów, które mają na sztywno określoną szerokość bezwzględną. Wszystkie wprowadzane elementy muszą się skalować razem ze stroną i mieścić nawet na ekranach telefonu. </p>
<br/>
<br/>
EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "udropship-help-pl-udropship-vendor-settings",
	"identifier" => "udropship-help-pl-udropship-vendor-settings",
  "content" => 
<<<EOT
udropship-help-pl-udropship-vendor-settings
EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "Panel vendora - Pomoc - Ustawienia - Ustawienia podstawowe",
	"identifier" => "udropship-help-pl-udropship-vendor-settings-info",
  "content" => 
<<<EOT
<h4>USTAWIENIA PODSTAWOWE</h4>
<p>Jest to miejsce na wprowadzenie podstawowych informacji dotyczących Twojej firmy,  niezbędnych do rozpoczęcia współpracy. Na podstawie danych dotyczących firmy, przygotowywane są wszystkie dokumenty – umowa, faktury etc. Dane kontaktowe firmy i osoby odpowiedzialnej za współpracę z nami, pozwolą nam usprawnić komunikację i obieg informacji. Jest tutaj również miejsce na wskazanie osoby odpowiedzialnej za administrację systemem, która będzie posiadało pełne uprawnienia do zarządzania kontem firmowym, dodawania innych użytkowników i określania dla nich ról. </p>

EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "Panel vendora - Pomoc - Ustawienia - RMA",
	"identifier" => "udropship-help-pl-udropship-vendor-settings-rma",
  "content" => 
<<<EOT
<h4>REKLAMACJE I ZWROTY </h4>
<p>Tutaj znajdują się wszystkie informacje niezbędne do sprawnej obsługi zwrotów i reklamacji.</p>

<p>Podaj adres do zwrotów – adres punktu, do którego zwracane produkty mają być odsyłane. </p>


<p>Wypełnij dane kontaktowe działu odpowiedzialnego za zwroty i reklamacje – adres e-mail oraz numer telefonu. To pod ten adres będą szły wiadomości związane ze zgłoszeniami zwrotów i reklamacji.</p>


<p>Podaj też dane kontaktowe do osoby odpowiedzialnej za zwroty i reklamacje, z którą możemy się skontaktować w razie pytań czy problemów. Jeśli osoba ta nie posiada telefonu stacjonarnego skopiuj w pole telefonu stacjonarnego numer telefonu komórkowego. </p>


<p>Wybierz spedytora, który będzie obsługiwał zwroty. Domyślnie system wybiera firmę kurierską obsługującą zamówienia także do obsługi zwrotów, jeśli jednak chcesz wybrać innego spedytora, zmień ustawienie „Użyj innego konta … do zwrotów” na „Tak” i podaj dane spedytora. </p>


EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "Panel vendora - Pomoc - Ustawienia - Sposoby dostawy",
	"identifier" => "udropship-help-pl-udropship-vendor-settings-shipping",
  "content" => 
<<<EOT
<h4>SPOSOBY DOSTAWY</h4>
<p>Tutaj wybierasz przewoźników, którzy będą obsługiwać Twoje zamówienia.</p><p> Najlepszą jakość obsługi zapewni Ci korzystanie z usługi firmy kurierskiej DHL, z którą nasz system jest już w pełni zintegrowany, i z którą podpisaliśmy korzystną bardzo umowę o współpracy. Jeśli jednak nie chcesz skorzystać z umowy, którą z tym spedytorem podpisaliśmy, możesz zamiast tego korzystać z usług własnego, dotychczasowego partnera. </p>
<p>Jeśli chcesz skorzystać z usług firmy DHL za pośrednictwem Modago, zmień ustawienie „Wysyłaj przez DHL” na „Tak” i wpisz w poszczególne rubryki otrzymane od nas dane klienta.</p>
<p>Jeśli chcesz skorzystać z usług firmy DHL w ramach własnej umowy ze spedytorem, zmień ustawienie „Wysyłaj przez DHL” na „Tak” i wpisz w poszczególne rubryki własne dane klienta. </p>
<p>Jeśli chcesz skorzystać z usług firmy UPS w ramach własnej umowy ze spedytorem, zmień ustawienie „Wysyłaj przez UPS” na „Tak” i wpisz w poszczególne rubryki własne dane klienta. </p>

EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "udropship-help-pl-urma",
	"identifier" => "udropship-help-pl-urma",
  "content" => 
<<<EOT
udropship-help-pl-urma
EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "Panel vendora - Pomoc - RMA - Obsługa reklamacji",
	"identifier" => "udropship-help-pl-urma-vendor",
  "content" => 
<<<EOT
<h4>OBSŁUGA ZWROTÓW I REKLAMACJI </h4>

<p>Zwroty i reklamacje są zgłaszane przez klientów przez specjalnie dedykowany panel na stronie, w którym wskazują zamówienia, potem produkt oraz przyczynę zwrotu lub reklamacji. Usprawnia to znacząco proces i zapewnia jak najlepszą jakość obsługi. System przyjmuje jedynie te zgłoszenia, które mieszczą się w dopuszczalnym dla Twoich produktów przedziale dat  i wyświetlane na karcie produktu. Jeśli czas na zwrot lub reklamację minął, klient otrzyma wyjaśnienie dlaczego nie może wysłać zgłoszenia. Wszystkie zgłoszenia trafiają natychmiast do panelu Obsługi reklamacji sklepu. W panelu możesz łatwo filtrować zgłoszenia po przyczynie i aktualnym statusie realizacji zgłoszenia. Zamówienia, które nie zostały odebrane przez klienta i zostały zwrócone przez firmę kurierską także trafiają do panelu jako zwroty z powodu nieodebranych przesyłek. 
<br/>
Możliwe przyczyny zgłoszenia to:
<ul style="padding-left:20px">
<li>dostawa niewłaściwego produktu</li>
<li>produkt za mały</li>
<li>produkt za duży</li>
<li>produkt dotarł uszkodzony</li>
<li>nieodebrana przesyłka </li>
<li>reklamacja</li>
</ul>

<br/>
<h5>SZCZEGÓŁY ZGŁOSZEŃ </h5>
<p>Na stronie szczegółów zgłoszeń możesz:
<ul style="padding-left:20px"><li>obejrzeć szczegóły reklamacji</li>
<li>obejrzeć szczegóły zamówienia związanego z reklamacją i przejść do zamówienia</li>
<li>zmodyfikować dane adresowe klienta</li>
<li>zmienić status zgłoszenia i wygenerować list przewozowy (w sytuacji dosyłania czegoś do klienta)</li>
<li>dodać komentarz do zgłoszenia</li>
<li>korespondować z klientem w sprawie reklamacji</li>
<li>wykonać zwrot płatności do klienta</li>
</ul></p>

<br/>
<h5>ZMIANA STATUSU ZGŁOSZENIA</h5>
<p>Aby zmienić status zgłoszenia, w szczegółach zgłoszenia, w sekcji „Zmień status / dodaj komentarz” wybierz nowy status  z listy rozwijanej. Na liście zobaczysz jedynie te opcje, które pasują do typu zgłoszenia i aktualnego stanu zgłoszenia.  
Poniżej objaśnienie wszystkich statusów:

<ul style="padding-left:20px">

<p><li><b>Nowe</b>- nowe zgłoszenie, wymaga od Ciebie decyzji i działania, z takim statusem pojawiają się zgłoszenia reklamacyjne i zgłoszenia zwrotu nieodebranych przesyłek. Jeśli zgłoszenie zawiera za mało informacji, należy wysłać wiadomość do klienta z prośbą o uzupełnienie danych.</li></p>

<p><li><b>Oczekuje na zamówienie kuriera</b> – to jest status, który mówi klientowi, że powinien zamówić kuriera po odbiór produktu. Ten status ustawiasz dla nowych reklamacji, które na podstawie zgłoszenia uznasz za uzasadnione. Klient otrzyma maila z prośbą o zamówienie kuriera i linkiem do strony, na której może to zrobić. Przy zmianie na ten status należy wprowadzić dla klienta wiadomość z informacją w jaki sposób reklamacja będzie realizowana. </li></p>

<p><li><b>Oczekuje na nadanie przesyłki </b>– to jest status po tym jak klient zamówił już kuriera – określił miejsce i termin odbioru przesyłki. Zgłoszenie pozostaje w tym statusie aż do momentu odbioru przesyłki przez kuriera. </li></p>

<p><li><b>Oczekuje na przesyłkę</b> – to jest status, który pojawia się gdy kurier odbierze przesyłkę od klienta i jest ona w drodze do Ciebie. Oznacza to, że przesyłka jest w drodze. </li></p>

<p><li><b>Otrzymana przesyłka</b> – status ten ustawiasz w momencie odbioru przesyłki zwrotnej/ reklamacyjnej od kuriera. Informuje on klienta o tym, że przesyłka dotarła i że może się niebawem spodziewać reakcji z Twojej strony.</li></p>

<p><li><b>Potwierdzona realizacja </b>– to jest status, który potwierdza, że reklamacja lub zwrot będą realizowane. Status ten ustawiasz dla reklamacji i zwrotów, gdy już odbierzesz zwracany/ reklamowany produkt i potwierdzisz ostatecznie, że zgłoszenie jest uzasadnione – produkt został zwrócony we właściwym stanie lub posiada zgłaszaną przez klienta wadę. </li></p>

<p><li><b>W trakcie wyjaśniania</b> – ten status ustawiasz, jeśli brakuje Ci informacji lub w jakiejkolwiek sytuacji problematycznej. Ta zmiana statusu nie generuje automatycznego maila do klienta – wymaga jednak napisania własnej wiadomości. Jest to status, który pozwoli Ci wstrzymać proces zwrotu lub reklamacji i ewentualnie cofnąć się do poprzedniego kroku. </li></p>

<p><li><b>Odrzucona realizacja</b> – to jest status informujący klienta o tym, że zgłoszenie nie będzie realizowane, że z jakiegoś względu jest niezgodne z warunkami zwrotów i reklamacji. Zmiana na ten status nie generuje automatycznego maila do klienta – wymaga jednak napisania własnej wiadomości wyjaśniającej.</li></p>

<p><li><b>Zamknięte – zrealizowane</b> – ten status ustawiany jest automatycznie, gdy wykonasz zwrot płatności do klienta i system utworzy odpowiednią transakcję w systemie płatności.</li></p>

<p><li><b>Zamknięte – niezrealizowane </b>– ten status ustawiasz, gdy już zakończysz proces zwrotu lub reklamacji dla zgłoszeń, które zostały odrzucone. </li></p>
</ul> </p>

<br/>
<h5>KOMUNIKACJA Z KLIENTEM </h5>
<p>Przy najważniejszych zmianach statusu, automatycznie generowane są maile do klienta informujące o zmianie w zgłoszeniu. Jeśli chcesz do maila dołączyć własną wiadomość, zaznacz pole „Wyślij do klienta”. Wszystkie treści wpisane w to pole są automatycznie zapisywane w historii zmian zamówienia. Jeśli wpiszesz komentarz bez zaznaczenia pola „Wyślij do klienta”, treść będzie widoczna tylko dla Ciebie, jako wewnętrzny komentarz do zamówienia. Możesz wysyłać wiadomości do klienta w dowolnym momencie, warto to  jednak robić wraz ze zmianą statusu, żeby klient nie otrzymał za dużo niezależnych wiadomości. W przypadku statusów: „W trakcie wyjaśniania” i „Zamknięte – niezrealizowane”, wymagana jest wiadomość wyjaśniająca dla klienta. </p>

<br/>
<h5>ZWROTY OD KLIENTÓW </h5>
<p>Zgłoszenia dotyczące zwrotów od klientów, o ile spełniają określone przez sprzedawcę warunki, przyjmowane są automatycznie - osoba zgłaszająca chęć zwrotu od razu zamawia odbiór przesyłki przez kuriera. 
<ul style="padding-left:20px">
<li>W panelu zobaczysz nowe zgłoszenie ze statusem „Oczekuje na nadanie przesyłki”. </li>
<li>Po przekazaniu przesyłki kurierowi, status zgłoszenia zmieni się na „Oczekuje na przesyłkę”.</li>
<li>W momencie, gdy odbierzesz zwrot zmień status na „Otrzymana przesyłka” – Klient dostanie informację o tym, że przesyłka dotarła.  </li>
<li>Jeśli wszystko się zgadza, potwierdź zwrot zmieniając status na „Potwierdzona realizacja”.</li>
<li>Na tym etapie, w szczegółach reklamacji przy pozycji "Zwrot płatności", możesz uruchomić proces zwrotu płatności za zwrócony produkt. Otworzy się okienko zwrotu płatności. Zaznacz produkty, za które należy się zwrot płatności i zatwierdź zwrot. </li>
<li>Gdy zwrot zostanie już w zrealizowany do końca, zamówienie automatycznie zmieni status na „Zamknięte – zrealizowane”.  </li>
</ul>
W sytuacji, gdy zwrot nie jest uzasadniony (np. produkt jest uszkodzony, używany, klient zwrócił towar inny niż zakupił):
<ul style="padding-left:20px">
<li>zmień status zgłoszenia na „w trakcie wyjaśniania” i wyślij wiadomość do klienta dopytując o szczegóły (w szczegółach zgłoszenia, bez zmiany statusu, dodaj komentarz i zaznacz pole „Wyślij do klienta”). Całą historię korespondencji będziesz widzieć w szczegółach reklamacji, na dole w Historii zmian reklamacji. </li>
<li>jeśli mimo wyjaśnień z klientem uznasz, że zwrot nie może być przyjęty zmień status na „Odrzucona realizacja” i prześlij klientowi bardzo dokładne wyjaśnienie przyczyny. Możesz w systemie Modago.pl wydrukować zwrotny list przewozowy i odesłać towar klientowi.</li>
<li>na koniec zmień status „Zamknięte – niezrealizowane”.  Po zmianie na ten status nie będziesz mógł już zmieniać statusu zgłoszenia ani drukować listów przewozowych.</li>
</ul>
</p>
<br/>

<h5>ZWROTY NIEODEBRANYCH ZAMÓWIEŃ</h5>
<p>Jeśli klient z jakiegoś powodu nie odbierze zamówienia, firma kurierska realizuję zwrot do nadawcy.
<ul style="padding-left:20px">
<li>Zgłoszenie z przyczyną zgłoszenia „Nieodebrana przesyłka” trafi do Twojego panelu Obsługi zwrotów i reklamacji ze statusem „Nowe”. </li>
<li>Musisz potwierdzić zwrot zmieniając jego status na „Potwierdzona realizacja”. Przesyłka zostanie wtedy do Ciebie nadana.   </li>
<li>Jeśli zwrócony produkt był już opłacony, możesz po odebraniu przesyłki (w szczegółach reklamacji przy pozycji "Zwrot płatności") uruchomić proces zwrotu płatności. Otworzy się okienko zwrotu płatności. Zaznacz produkty, za które należy się zwrot płatności i zatwierdź zwrot. </li>
<li>Po odebraniu zwrotu i dokonaniu ewentualnego zwrotu płatności możesz zamknąć zgłoszenie zmieniając jego status na „Zamknięte – zrealizowane”. </li>
</ul></p>

<br/>
<h5>REKLAMACJE</h5>
<p>Jeśli klient wybrał jako przyczynę zgłoszenia reklamację, musisz potwierdzić przyjęcie zgłoszenia, aby produkt mógł zostać odesłany. Jeśli masz jakieś pytania, możesz poprosić o dosłanie informacji. Możesz też za pomocą systemu dosłać dodatkowy/brakujący element,  produkt na wymianę lub dokonać zwrotu pieniędzy.
Zgłoszenia reklamacyjne wpadają do systemu ze statusem „Nowe”. Zapoznaj się z opisem problemu w zgłoszeniu.
Jeśli uznasz że reklamacja jest uzasadniona i klient powinien odesłać produkt:
<ul style="padding-left:20px">
<li>Zmień status na „Oczekuje na zamówienie kuriera”. Klient otrzyma informację o przyjęciu zgłoszenia i instrukcję jak zamówić kuriera. </li>
<li>W momencie, gdy kurier odbierze paczkę, status reklamacji zmieni się na „Oczekuje na przesyłkę”.</li>
<li>Gdy odbierzesz zwrot zmień status na „Otrzymana przesyłka” – Klient dostanie informację o tym, że przesyłka dotarła.  </li>
<li>Jeśli wszystko się zgadza, potwierdź zwrot zmieniając status na „Potwierdzona realizacja”.</li>
<li>Na tym etapie, w szczegółach reklamacji przy pozycji "Zwrot płatności", możesz uruchomić proces zwrotu płatności za zwrócony produkt. Otworzy się okienko zwrotu płatności. Zaznacz produkty, za które należy się zwrot płatności i zatwierdź zwrot. Jeśli zostało z klientem ustalone, że otrzyma tylko częściowy zwrot, możesz zmodyfikować pole wartości. </li>
<li>Po dokonaniu zwrotu płatności możesz zamknąć zgłoszenie zmieniając jego status na „Zamknięte – zrealizowane”. </li>
</ul>
</p>

<p>Jeśli uznasz że reklamacja jest nieuzasadniona, w zgłoszeniu brakuje informacji, masz jakieś pytania lub wątpliwości:
<ul style="padding-left:20px">
<li>zmień status zgłoszenia na „w trakcie wyjaśniania” i wyślij wiadomość do klienta dopytując o szczegóły (w szczegółach zgłoszenia, bez zmiany statusu, dodaj komentarz i zaznacz pole „Wyślij do klienta”). Całą historię korespondencji będziesz widzieć w szczegółach reklamacji, na dole w Historii zmian reklamacji.  </li>
<li>jeśli mimo wyjaśnień z klientem uznasz, że zwrot nie może być przyjęty zmień status na „Odrzucona realizacja” i prześlij klientowi bardzo dokładne wyjaśnienie przyczyny. Możesz w systemie Modago.pl wydrukować zwrotny list przewozowy i odesłać towar klientowi.</li>
<li>na koniec zmień status „Zamknięte – niezrealizowane”.  Po zmianie na ten status nie będziesz mógł już zmieniać statusu zgłoszenia ani drukować listów przewozowych.</li>
</ul> 
</p>

<p>Jeśli potrzebujesz coś dosłać klientowi:
<ul style="padding-left:20px">
<li>możesz wydrukować list przewozowy z poziomu szczegółów zgłoszenia w sekcji „Przesyłki”. </li>
<li>w sekcji przesyłek na dole będziesz widzieć wszystkie dodatkowe listy przewozowe i statusy przesyłki.</li>
<li>pamiętaj by wszystkie szczegóły ustalić z klientem.</li>
<li>pamiętaj by wszystkie szczegóły ustalić z klientem.</li>
<li>w momencie, gdy klient odbierze produkt i uznasz, że reklamacja została zakończona, zmień jej status na „Zamknięte – zrealizowane”.  </li>
</ul></p>

<br/>
<h5>ZWROT PŁATNOŚCI</h5>
<p>W szczegółach zgłoszenia, w sekcji „Szczegóły reklamacji” znajdziesz pozycję „Zwrot płatności”. Jeśli nie zostały zwrócone w ramach zgłoszenia żadne pieniądze, widać pole „Zwrot niewykonany”, jeśli została dokonana płatność – widać wartość zwrotu. Zwrot może być dokonany jedynie w statusie zgłoszenia „Potwierdzona realizacja” – wtedy pole to stanie się aktywne, pojawi się strzałeczka rozwijające menu z opcją „Utwórz zwrot”.  Gdy wybierzesz tę opcję, otworzy się okienko zwrotu płatności. Zaznacz produkty, za które należy się zwrot płatności i zatwierdź zwrot. Jeśli zostało z klientem ustalone, że otrzyma tylko częściowy zwrot, możesz zmodyfikować pole wartości. Wartość zwrotu nie może przekraczać wartości (ceny sprzedaży) zwracanego produktu. </p>

<p> Jeśli Klient dokonał płatności za zamówienie przelewem lub kartą, pieniądze zostaną automatycznie przelane przez system Modago.pl na numer rachunku bankowego, z którego przyszła płatność za zamówienie. Odbywa się to automatycznie w systemie Modago.pl. </p>

</p>Jeśli klient składał zamówienie za pobraniem, zwrot płatności jest realizowany przez Ciebie na konto które podał klient podczas procesu zgłaszania zwrotu, a w systemie Modago.pl rejestrujesz jedynie ten fakt. Konto bankowe klienta pokazywane jest po prawej na górze, w sekcji „Klient”. Jeśli klient nie podał numeru konta do zwrotu, musisz sam ustalić z klientem to konto.</p>

<br/><br/>
EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "Panel vendora - Pomoc - RMA - Szczegóły reklamacji",
	"identifier" => "udropship-help-pl-urma-vendor-edit",
  "content" => 
<<<EOT
<h4>SZCZEGÓŁY ZGŁOSZEŃ ZWROTU/ REKLAMACJI</h4>

<p>Na stronie szczegółów zgłoszeń możesz:
<ul style="padding-left:20px"><li>obejrzeć szczegóły reklamacji</li>
<li>obejrzeć szczegóły zamówienia związanego z reklamacją i przejść do zamówienia</li>
<li>zmodyfikować dane adresowe klienta</li>
<li>zmienić status zgłoszenia i wygenerować list przewozowy (w sytuacji dosyłania czegoś do klienta)</li>
<li>dodać komentarz do zgłoszenia</li>
<li>korespondować z klientem w sprawie reklamacji</li>
<li>wykonać zwrot płatności do klienta</li>
</ul></p>

<br/>
<h5>ZMIANA STATUSU ZGŁOSZENIA</h5>
<p>Aby zmienić status zgłoszenia, w szczegółach zgłoszenia, w sekcji „Zmień status / dodaj komentarz” wybierz nowy status  z listy rozwijanej. Na liście zobaczysz jedynie te opcje, które pasują do typu zgłoszenia i aktualnego stanu zgłoszenia.  
Poniżej objaśnienie wszystkich statusów:

<ul style="padding-left:20px">

<p><li><b>Nowe</b>- nowe zgłoszenie, wymaga od Ciebie decyzji i działania, z takim statusem pojawiają się zgłoszenia reklamacyjne i zgłoszenia zwrotu nieodebranych przesyłek. Jeśli zgłoszenie zawiera za mało informacji, należy wysłać wiadomość do klienta z prośbą o uzupełnienie danych.</li></p>

<p><li><b>Oczekuje na zamówienie kuriera</b> – to jest status, który mówi klientowi, że powinien zamówić kuriera po odbiór produktu. Ten status ustawiasz dla nowych reklamacji, które na podstawie zgłoszenia uznasz za uzasadnione. Klient otrzyma maila z prośbą o zamówienie kuriera i linkiem do strony, na której może to zrobić. Przy zmianie na ten status należy wprowadzić dla klienta wiadomość z informacją w jaki sposób reklamacja będzie realizowana. </li></p>

<p><li><b>Oczekuje na nadanie przesyłki </b>– to jest status po tym jak klient zamówił już kuriera – określił miejsce i termin odbioru przesyłki. Zgłoszenie pozostaje w tym statusie aż do momentu odbioru przesyłki przez kuriera. </li></p>

<p><li><b>Oczekuje na przesyłkę</b> – to jest status, który pojawia się gdy kurier odbierze przesyłkę od klienta i jest ona w drodze do Ciebie. Oznacza to, że przesyłka jest w drodze. </li></p>

<p><li><b>Otrzymana przesyłka</b> – status ten ustawiasz w momencie odbioru przesyłki zwrotnej/ reklamacyjnej od kuriera. Informuje on klienta o tym, że przesyłka dotarła i że może się niebawem spodziewać reakcji z Twojej strony.</li></p>

<p><li><b>Potwierdzona realizacja </b>– to jest status, który potwierdza, że reklamacja lub zwrot będą realizowane. Status ten ustawiasz dla reklamacji i zwrotów, gdy już odbierzesz zwracany/ reklamowany produkt i potwierdzisz ostatecznie, że zgłoszenie jest uzasadnione – produkt został zwrócony we właściwym stanie lub posiada zgłaszaną przez klienta wadę. </li></p>

<p><li><b>W trakcie wyjaśniania</b> – ten status ustawiasz, jeśli brakuje Ci informacji lub w jakiejkolwiek sytuacji problematycznej. Ta zmiana statusu nie generuje automatycznego maila do klienta – wymaga jednak napisania własnej wiadomości. Jest to status, który pozwoli Ci wstrzymać proces zwrotu lub reklamacji i ewentualnie cofnąć się do poprzedniego kroku. </li></p>

<p><li><b>Odrzucona realizacja</b> – to jest status informujący klienta o tym, że zgłoszenie nie będzie realizowane, że z jakiegoś względu jest niezgodne z warunkami zwrotów i reklamacji. Zmiana na ten status nie generuje automatycznego maila do klienta – wymaga jednak napisania własnej wiadomości wyjaśniającej.</li></p>

<p><li><b>Zamknięte – zrealizowane</b> – ten status ustawiany jest automatycznie, gdy wykonasz zwrot płatności do klienta i system utworzy odpowiednią transakcję w systemie płatności.</li></p>

<p><li><b>Zamknięte – niezrealizowane </b>– ten status ustawiasz, gdy już zakończysz proces zwrotu lub reklamacji dla zgłoszeń, które zostały odrzucone. </li></p>
</ul> </p>

<br/>
<h5>KOMUNIKACJA Z KLIENTEM </h5>
<p>Przy najważniejszych zmianach statusu, automatycznie generowane są maile do klienta informujące o zmianie w zgłoszeniu. Jeśli chcesz do maila dołączyć własną wiadomość, zaznacz pole „Wyślij do klienta”. Wszystkie treści wpisane w to pole są automatycznie zapisywane w historii zmian zamówienia. Jeśli wpiszesz komentarz bez zaznaczenia pola „Wyślij do klienta”, treść będzie widoczna tylko dla Ciebie, jako wewnętrzny komentarz do zamówienia. Możesz wysyłać wiadomości do klienta w dowolnym momencie, warto to  jednak robić wraz ze zmianą statusu, żeby klient nie otrzymał za dużo niezależnych wiadomości. W przypadku statusów: „W trakcie wyjaśniania” i „Zamknięte – niezrealizowane”, wymagana jest wiadomość wyjaśniająca dla klienta. </p>

<br/>
<h5>ZWROTY OD KLIENTÓW </h5>
<p>Zgłoszenia dotyczące zwrotów od klientów, o ile spełniają określone przez sprzedawcę warunki, przyjmowane są automatycznie - osoba zgłaszająca chęć zwrotu od razu zamawia odbiór przesyłki przez kuriera. 
<ul style="padding-left:20px">
<li>W panelu zobaczysz nowe zgłoszenie ze statusem „Oczekuje na nadanie przesyłki”. </li>
<li>Po przekazaniu przesyłki kurierowi, status zgłoszenia zmieni się na „Oczekuje na przesyłkę”.</li>
<li>W momencie, gdy odbierzesz zwrot zmień status na „Otrzymana przesyłka” – Klient dostanie informację o tym, że przesyłka dotarła.  </li>
<li>Jeśli wszystko się zgadza, potwierdź zwrot zmieniając status na „Potwierdzona realizacja”.</li>
<li>Na tym etapie, w szczegółach reklamacji przy pozycji "Zwrot płatności", możesz uruchomić proces zwrotu płatności za zwrócony produkt. Otworzy się okienko zwrotu płatności. Zaznacz produkty, za które należy się zwrot płatności i zatwierdź zwrot. </li>
<li>Gdy zwrot zostanie już w zrealizowany do końca, zamówienie automatycznie zmieni status na „Zamknięte – zrealizowane”.  </li>
</ul>
W sytuacji, gdy zwrot nie jest uzasadniony (np. produkt jest uszkodzony, używany, klient zwrócił towar inny niż zakupił):
<ul style="padding-left:20px">
<li>zmień status zgłoszenia na „w trakcie wyjaśniania” i wyślij wiadomość do klienta dopytując o szczegóły (w szczegółach zgłoszenia, bez zmiany statusu, dodaj komentarz i zaznacz pole „Wyślij do klienta”). Całą historię korespondencji będziesz widzieć w szczegółach reklamacji, na dole w Historii zmian reklamacji. </li>
<li>jeśli mimo wyjaśnień z klientem uznasz, że zwrot nie może być przyjęty zmień status na „Odrzucona realizacja” i prześlij klientowi bardzo dokładne wyjaśnienie przyczyny. Możesz w systemie Modago.pl wydrukować zwrotny list przewozowy i odesłać towar klientowi.</li>
<li>na koniec zmień status „Zamknięte – niezrealizowane”.  Po zmianie na ten status nie będziesz mógł już zmieniać statusu zgłoszenia ani drukować listów przewozowych.</li>
</p>
<br/>

<h5>ZWROTY NIEODEBRANYCH ZAMÓWIEŃ</h5>
<p>Jeśli klient z jakiegoś powodu nie odbierze zamówienia, firma kurierska realizuję zwrot do nadawcy.
<ul style="padding-left:20px">
<li>Zgłoszenie z przyczyną zgłoszenia „Nieodebrana przesyłka” trafi do Twojego panelu Obsługi zwrotów i reklamacji ze statusem „Nowe”. </li>
<li>Musisz potwierdzić zwrot zmieniając jego status na „Potwierdzona realizacja”. Przesyłka zostanie wtedy do Ciebie nadana.   </li>
<li>Jeśli zwrócony produkt był już opłacony, możesz po odebraniu przesyłki (w szczegółach reklamacji przy pozycji "Zwrot płatności") uruchomić proces zwrotu płatności. Otworzy się okienko zwrotu płatności. Zaznacz produkty, za które należy się zwrot płatności i zatwierdź zwrot. </li>
<li>Po odebraniu zwrotu i dokonaniu ewentualnego zwrotu płatności możesz zamknąć zgłoszenie zmieniając jego status na „Zamknięte – zrealizowane”. </li>
</ul></p>

<br/>
<h5>REKLAMACJE</h5>
<p>Jeśli klient wybrał jako przyczynę zgłoszenia reklamację, musisz potwierdzić przyjęcie zgłoszenia, aby produkt mógł zostać odesłany. Jeśli masz jakieś pytania, możesz poprosić o dosłanie informacji. Możesz też za pomocą systemu dosłać dodatkowy/brakujący element,  produkt na wymianę lub dokonać zwrotu pieniędzy.
Zgłoszenia reklamacyjne wpadają do systemu ze statusem „Nowe”. Zapoznaj się z opisem problemu w zgłoszeniu.
Jeśli uznasz że reklamacja jest uzasadniona i klient powinien odesłać produkt:
<ul style="padding-left:20px">
<li>Zmień status na „Oczekuje na zamówienie kuriera”. Klient otrzyma informację o przyjęciu zgłoszenia i instrukcję jak zamówić kuriera. </li>
<li>W momencie, gdy kurier odbierze paczkę, status reklamacji zmieni się na „Oczekuje na przesyłkę”.</li>
<li>Gdy odbierzesz zwrot zmień status na „Otrzymana przesyłka” – Klient dostanie informację o tym, że przesyłka dotarła.  </li>
<li>Jeśli wszystko się zgadza, potwierdź zwrot zmieniając status na „Potwierdzona realizacja”.</li>
<li>Na tym etapie, w szczegółach reklamacji przy pozycji "Zwrot płatności", możesz uruchomić proces zwrotu płatności za zwrócony produkt. Otworzy się okienko zwrotu płatności. Zaznacz produkty, za które należy się zwrot płatności i zatwierdź zwrot. Jeśli zostało z klientem ustalone, że otrzyma tylko częściowy zwrot, możesz zmodyfikować pole wartości. </li>
<li>Po dokonaniu zwrotu płatności możesz zamknąć zgłoszenie zmieniając jego status na „Zamknięte – zrealizowane”. </li>
</ul>
</p>

<p>Jeśli uznasz że reklamacja jest nieuzasadniona, w zgłoszeniu brakuje informacji, masz jakieś pytania lub wątpliwości:
<ul style="padding-left:20px">
<li>zmień status zgłoszenia na „w trakcie wyjaśniania” i wyślij wiadomość do klienta dopytując o szczegóły (w szczegółach zgłoszenia, bez zmiany statusu, dodaj komentarz i zaznacz pole „Wyślij do klienta”). Całą historię korespondencji będziesz widzieć w szczegółach reklamacji, na dole w Historii zmian reklamacji.  </li>
<li>jeśli mimo wyjaśnień z klientem uznasz, że zwrot nie może być przyjęty zmień status na „Odrzucona realizacja” i prześlij klientowi bardzo dokładne wyjaśnienie przyczyny. Możesz w systemie Modago.pl wydrukować zwrotny list przewozowy i odesłać towar klientowi.</li>
<li>na koniec zmień status „Zamknięte – niezrealizowane”.  Po zmianie na ten status nie będziesz mógł już zmieniać statusu zgłoszenia ani drukować listów przewozowych.</li>
</ul> 
</p>

<p>Jeśli potrzebujesz coś dosłać klientowi:
<ul style="padding-left:20px">
<li>możesz wydrukować list przewozowy z poziomu szczegółów zgłoszenia w sekcji „Przesyłki”. </li>
<li>w sekcji przesyłek na dole będziesz widzieć wszystkie dodatkowe listy przewozowe i statusy przesyłki.</li>
<li>pamiętaj by wszystkie szczegóły ustalić z klientem.</li>
<li>pamiętaj by wszystkie szczegóły ustalić z klientem.</li>
<li>w momencie, gdy klient odbierze produkt i uznasz, że reklamacja została zakończona, zmień jej status na „Zamknięte – zrealizowane”.  </li>
</ul></p>

<br/>
<h5>ZWROT PŁATNOŚCI</h5>
<p>W szczegółach zgłoszenia, w sekcji „Szczegóły reklamacji” znajdziesz pozycję „Zwrot płatności”. Jeśli nie zostały zwrócone w ramach zgłoszenia żadne pieniądze, widać pole „Zwrot niewykonany”, jeśli została dokonana płatność – widać wartość zwrotu. Zwrot może być dokonany jedynie w statusie zgłoszenia „Potwierdzona realizacja” – wtedy pole to stanie się aktywne, pojawi się strzałeczka rozwijające menu z opcją „Utwórz zwrot”.  Gdy wybierzesz tę opcję, otworzy się okienko zwrotu płatności. Zaznacz produkty, za które należy się zwrot płatności i zatwierdź zwrot. Jeśli zostało z klientem ustalone, że otrzyma tylko częściowy zwrot, możesz zmodyfikować pole wartości. Wartość zwrotu nie może przekraczać wartości (ceny sprzedaży) zwracanego produktu. </p>

<p> Jeśli Klient dokonał płatności za zamówienie przelewem lub kartą, pieniądze zostaną automatycznie przelane przez system Modago.pl na numer rachunku bankowego, z którego przyszła płatność za zamówienie. Odbywa się to automatycznie w systemie Modago.pl. </p>

</p>Jeśli klient składał zamówienie za pobraniem, zwrot płatności jest realizowany przez Ciebie na konto które podał klient podczas procesu zgłaszania zwrotu, a w systemie Modago.pl rejestrujesz jedynie ten fakt. Konto bankowe klienta pokazywane jest po prawej na górze, w sekcji „Klient”. Jeśli klient nie podał numeru konta do zwrotu, musisz sam ustalić z klientem to konto.</p>
<br/><br/>

EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "udropship-help-en",
	"identifier" => "udropship-help-en",
  "content" => 
<<<EOT
udropship-help-en
EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "udropship-help-en-campaign",
	"identifier" => "udropship-help-en-campaign",
  "content" => 
<<<EOT
udropship-help-en-campaign
EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "udropship-help-en-campaign-placement",
	"identifier" => "udropship-help-en-campaign-placement",
  "content" => 
<<<EOT
udropship-help-en-campaign-placement
EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "udropship-help-en-campaign-placement-category",
	"identifier" => "udropship-help-en-campaign-placement-category",
  "content" => 
<<<EOT
udropship-help-en-campaign-placement-category
EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "udropship-help-en-campaign-vendor",
	"identifier" => "udropship-help-en-campaign-vendor",
  "content" => 
<<<EOT
udropship-help-en-campaign-vendor
EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "udropship-help-en-campaign-vendor-edit",
	"identifier" => "udropship-help-en-campaign-vendor-edit",
  "content" => 
<<<EOT
udropship-help-en-campaign-vendor-edit
EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "udropship-help-en-udpo",
	"identifier" => "udropship-help-en-udpo",
  "content" => 
<<<EOT
udropship-help-en-udpo
EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "udropship-help-en-udpo-vendor",
	"identifier" => "udropship-help-en-udpo-vendor",
  "content" => 
<<<EOT
udropship-help-en-udpo-vendor
EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "udropship-help-en-udpo-vendor-aggregated",
	"identifier" => "udropship-help-en-udpo-vendor-aggregated",
  "content" => 
<<<EOT
udropship-help-en-udpo-vendor-aggregated
EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "udropship-help-en-udpo-vendor-edit",
	"identifier" => "udropship-help-en-udpo-vendor-edit",
  "content" => 
<<<EOT
udropship-help-en-udpo-vendor-edit
EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "udropship-help-en-udprod",
	"identifier" => "udropship-help-en-udprod",
  "content" => 
<<<EOT
udropship-help-en-udprod
EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "udropship-help-en-udprod-vendor-attributes",
	"identifier" => "udropship-help-en-udprod-vendor-attributes",
  "content" => 
<<<EOT
udropship-help-en-udprod-vendor-attributes
EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "udropship-help-en-udprod-vendor-image",
	"identifier" => "udropship-help-en-udprod-vendor-image",
  "content" => 
<<<EOT
udropship-help-en-udprod-vendor-image
EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "udropship-help-en-udprod-vendor-price",
	"identifier" => "udropship-help-en-udprod-vendor-price",
  "content" => 
<<<EOT
udropship-help-en-udprod-vendor-price
EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "udropship-help-en-udprod-vendor-product",
	"identifier" => "udropship-help-en-udprod-vendor-product",
  "content" => 
<<<EOT
udropship-help-en-udprod-vendor-product
EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "udropship-help-en-udqa",
	"identifier" => "udropship-help-en-udqa",
  "content" => 
<<<EOT
udropship-help-en-udqa
EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "udropship-help-en-udqa-vendor",
	"identifier" => "udropship-help-en-udqa-vendor",
  "content" => 
<<<EOT
udropship-help-en-udqa-vendor
EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "udropship-help-en-udqa-vendor-questionEdit",
	"identifier" => "udropship-help-en-udqa-vendor-questionEdit",
  "content" => 
<<<EOT
udropship-help-en-udqa-vendor-questionEdit
EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "udropship-help-en-udqa-vendor-questions",
	"identifier" => "udropship-help-en-udqa-vendor-questions",
  "content" => 
<<<EOT
udropship-help-en-udqa-vendor-questions
EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "udropship-help-en-udropship",
	"identifier" => "udropship-help-en-udropship",
  "content" => 
<<<EOT
udropship-help-en-udropship
EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "udropship-help-en-udropship-ghapi",
	"identifier" => "udropship-help-en-udropship-ghapi",
  "content" => 
<<<EOT
udropship-help-en-udropship-ghapi
EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "udropship-help-en-udropship-operator",
	"identifier" => "udropship-help-en-udropship-operator",
  "content" => 
<<<EOT
udropship-help-en-udropship-operator
EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "udropship-help-en-udropship-operator-edit",
	"identifier" => "udropship-help-en-udropship-operator-edit",
  "content" => 
<<<EOT
udropship-help-en-udropship-operator-edit
EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "udropship-help-en-udropship-pos",
	"identifier" => "udropship-help-en-udropship-pos",
  "content" => 
<<<EOT
udropship-help-en-udropship-pos
EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "udropship-help-en-udropship-pos-edit",
	"identifier" => "udropship-help-en-udropship-pos-edit",
  "content" => 
<<<EOT
udropship-help-en-udropship-pos-edit
EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "udropship-help-en-udropship-sizetable",
	"identifier" => "udropship-help-en-udropship-sizetable",
  "content" => 
<<<EOT
udropship-help-en-udropship-sizetable
EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "udropship-help-en-udropship-sizetable-edit",
	"identifier" => "udropship-help-en-udropship-sizetable-edit",
  "content" => 
<<<EOT
udropship-help-en-udropship-sizetable-edit
EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "udropship-help-en-udropship-vendor-settings",
	"identifier" => "udropship-help-en-udropship-vendor-settings",
  "content" => 
<<<EOT
udropship-help-en-udropship-vendor-settings
EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "udropship-help-en-udropship-vendor-settings-info",
	"identifier" => "udropship-help-en-udropship-vendor-settings-info",
  "content" => 
<<<EOT
udropship-help-en-udropship-vendor-settings-info
EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "udropship-help-en-udropship-vendor-settings-rma",
	"identifier" => "udropship-help-en-udropship-vendor-settings-rma",
  "content" => 
<<<EOT
udropship-help-en-udropship-vendor-settings-rma
EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "udropship-help-en-udropship-vendor-settings-shipping",
	"identifier" => "udropship-help-en-udropship-vendor-settings-shipping",
  "content" => 
<<<EOT
udropship-help-en-udropship-vendor-settings-shipping
EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "udropship-help-en-urma",
	"identifier" => "udropship-help-en-urma",
  "content" => 
<<<EOT
udropship-help-en-urma
EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "udropship-help-en-urma-vendor",
	"identifier" => "udropship-help-en-urma-vendor",
  "content" => 
<<<EOT
udropship-help-en-urma-vendor
EOT
,
	"is_active" => 1,
	"stores" => 0,
);
$blocks[] = array (
	"title" => "udropship-help-en-urma-vendor-edit",
	"identifier" => "udropship-help-en-urma-vendor-edit",
  "content" => 
<<<EOT
udropship-help-en-urma-vendor-edit
EOT
,
	"is_active" => 1,
	"stores" => 0,
);

foreach ($blocks as $blockData) {
    $collection = Mage::getModel('cms/block')->getCollection();
    $collection->addFieldToFilter('identifier',$blockData["identifier"]);
    $currentBlock = $collection->getFirstItem();

    if ($currentBlock->getBlockId()) {
        $oldBlock = $currentBlock->getData();
	    $blockData = array_merge($oldBlock, $blockData);
    }
	$currentBlock->setData($blockData)->save();
}

