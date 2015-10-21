<?php

class Gh_Wfirma_TestController extends Mage_Core_Controller_Front_Action {
	public function indexAction() {
		/** @var GH_Wfirma_Model_Client $client */
		$client = Mage::getModel('ghwfirma/client');

		error_reporting(E_ALL);
		ini_set("display_errors", 1);

		ini_set('xdebug.var_display_max_depth', 256);
		ini_set('xdebug.var_display_max_children', 1024);
		ini_set('xdebug.var_display_max_data', 5000);

		$post = array(
			'contractors' => array(
				'contractor' => array(
					//dane firmy
					'name'          => 'Testowy Contractor Edytowany', //nazwa długa
					'altname'       => 'TestContrEdit', //nazwa skrócona
					'nip'           => 1691732219,

					//adres główny
					'street'        => 'Plac Defilad 1123123',
					'zip'           => '00-901',
					'city'          => 'Warszawawa',
					'country'       => 'PL', //dwuliterowy kod kraju (http://www.finanse.mf.gov.pl/documents/766655/1198699/KodyKrajow_v3-0.xsd)

					//dane kontaktowe
					'phone'         => 123123123,
					'skype'         => 'skypelogin',
					'fax'           => 123123123,
					'email'         => 'adam.wilk+wfirma@convertica.pl', //potrzebny do mailowych powiadomien o fakturach
					'url'           => 'http://www.google.pl',
					'description'   => 'jakiś tam opis firmy',

					//inne
					'buyer'         => 1, //Wartość 1 dla oznaczenia, że kontrahent jest nabywcą
					'seller'        => 0, //Wartość 1 dla oznaczenia, że kontrahent jest dostawcą
					'account_number'=> '02297745003044811546527422',

					//Domyślna wartość rabatu w procentach, która będzie stosowana dla kontrahenta. Dla rabatu 50% należy wprowadzić wartość 50.
					//Nie obowiązuje przy produktach oznaczonych jako 'nierabatowalne' ;)
					'discount_percent'=> 0,

					//płatności
					'payment_days'  => 7, //domyślny termin płatności
					'payment_method'=> GH_Wfirma_Model_Client::INVOICE_PAYMENT_METHOD_TRANSFER, //domyślna metoda płatności

					//W przypadku wartości 1 i włączonych automatycznych powiadomieniach o niezapłaconych fakturach,
					//kontrahent otrzyma monit w przypadku braku zapłaty za fakturę.
					'remind'        => 1,

					//Wartość hasha zabezpieczającego panel klienta (dostępnego przez odsyłacz http://wfirma.pl/invoice_externals/find/HASH).
					//nie ustawiałem tego - system sam generuje ten hash
					//'hash'          => 'iisdfiuashdfiuahsiduhfiasudhf',

					//inny adres kontaktowy
					'different_contact_address' => 0, //czy adres kontaktowy różni się od adresu głównego. 1 - TAK, 0 - NIE
					/* jezeli 1 wyzej to:
					'contact_name' => 'Testowy Contractor Kontakt',
					'contact_street' => 'Plac Defilad 2',
					'contact_zip' => '00-901',
					'contact_city' => 'Warszawa',
					'contact_country' => 'PL', //dwuliterowy kod kraju (http://www.finanse.mf.gov.pl/documents/766655/1198699/KodyKrajow_v3-0.xsd)
					'contact_person' => 'Osoba kontaktowa'
					*/
				)
			)
		);

		var_dump($client->editContractor(5249337,$post));



		return;
	}
}