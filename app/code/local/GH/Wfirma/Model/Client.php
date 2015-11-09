<?php
class GH_Wfirma_Model_Client {

	//authentication config paths
	const API_URL_CONFIG_PATH               = 'ghwfirma/authentication/api_url';
	const LOGIN_CONFIG_PATH                 = 'ghwfirma/authentication/login';
	const PASSWORD_CONFIG_PATH              = 'ghwfirma/authentication/password';

	//wFirma invoice items ids config paths
	const ITEM_ID_COMMISSION_CONFIG_PATH    = 'ghwfirma/invoice_items/commission';
	const ITEM_ID_TRANSPORT_CONFIG_PATH     = 'ghwfirma/invoice_items/transport';
	const ITEM_ID_MARKETING_CONFIG_PATH     = 'ghwfirma/invoice_items/marketing';
	const ITEM_ID_OTHER_CONFIG_PATH         = 'ghwfirma/invoice_items/other';

	//wFirma result status code success
	const RESULT_STATUS_CODE_OK                     = 'OK';

	//Wywoływana akcja nie istnieje. Sprawdź czy w poprawny sposób podałeś odnośnik.
	const RESULT_STATUS_CODE_ACTION_NOT_FOUND       = 'ACTION NOT FOUND';

	//Podany obiekt nie istnieje.
	const RESULT_STATUS_CODE_NOT_FOUND              = 'NOT FOUND';

	//Wykonanie akcji wymaga podania nazwy użytkownika i hasła. Ten błąd wyświetla się także w przypadku niepoprawnej nazwy użytkownika lub hasła.
	const RESULT_STATUS_CODE_AUTH                   = 'AUTH';

	//Wewnętrzny błąd API. Nie powinien nastąpić. Takie zdarzenia będą monitorowane i analizowane indywidualnie.
	const RESULT_STATUS_CODE_FATAL                  = 'FATAL';

	//Podane dane wejściowe są niepoprawne. Np. struktura XML jest nieprawidłowa.
	const RESULT_STATUS_CODE_INPUT_ERROR            = 'INPUT ERROR';

	//Podczas próby dodania lub modyfikacji obiektu wystąpiły błędy walidacji. Szczegółowe informacje na temat błędów walidacji znajdują się niżej.
	const RESULT_STATUS_CODE_ERROR                  = 'ERROR';

	//Serwis API tymczasowo wyłączony. Proszę spróbować później. Wyłączenia serwisu można się spodziewać podczas aktualizacji wfirma.pl lub samego API.
	const RESULT_STATUS_CODE_OUT_OF_SERVICE         = 'OUT OF SERVICE';

	//Próba wywołania zakresu do którego nie ma się dostępu (tylko przy autoryzacji przez OAuth).
	const RESULT_STATUS_CODE_DENIED_SCOPE_REQUESTED = 'DENIED SCOPE REQUESTED';

	//limit po stronie wFirma to 512 znaków, ale każdy znak nowej linii zamieniany jest na <br/> co zmniejsza limit, więc ustawiam go na trochę niższy
	const NOTE_FIELD_LENGTH = 450;



	private $_inputFormat                   = 'json'; //other options: xml (default) and php (serialized php array),
	private $_outputFormat                  = 'json'; //other options as above
	private $_allowedDataFormats            = array('xml','json','php');

	private $_apiUrl                        = false;
	private $_login                         = false;
	private $_password                      = false;
	private $_itemIdCommission              = false;
	private $_itemIdTransport               = false;
	private $_itemIdMarketing               = false;
	private $_itemIdOther                   = false;

	private $_helper;

	public function __construct() {
		//do nothing for now
	}

	private function getLogin() {
		if(!$this->_login) {
			$this->_login = Mage::getStoreConfig(self::LOGIN_CONFIG_PATH);
		}
		return $this->_login;
	}

	private function getPassword() {
		if(!$this->_password) {
			$this->_password = Mage::getStoreConfig(self::PASSWORD_CONFIG_PATH);
		}
		return $this->_password;
	}

	public function getItemIdCommission() {
		if(!$this->_itemIdCommission) {
			$this->_itemIdCommission = Mage::getStoreConfig(self::ITEM_ID_COMMISSION_CONFIG_PATH);
		}
		return $this->_itemIdCommission;
	}

	public function getItemIdTransport() {
		if(!$this->_itemIdTransport) {
			$this->_itemIdTransport = Mage::getStoreConfig(self::ITEM_ID_TRANSPORT_CONFIG_PATH);
		}
		return $this->_itemIdTransport;
	}

	public function getItemIdMarketing() {
		if(!$this->_itemIdMarketing) {
			$this->_itemIdMarketing = Mage::getStoreConfig(self::ITEM_ID_MARKETING_CONFIG_PATH);
		}
		return $this->_itemIdMarketing;
	}

	public function getItemIdOther() {
		if(!$this->_itemIdOther) {
			$this->_itemIdOther = Mage::getStoreConfig(self::ITEM_ID_OTHER_CONFIG_PATH);
		}
		return $this->_itemIdOther;
	}

	private function getApiUrl() {
		if(!$this->_apiUrl) {
			$this->_apiUrl = Mage::getStoreConfig(self::API_URL_CONFIG_PATH);
		}
		return $this->_apiUrl;
	}

	public function getInputFormat() {
		return $this->_inputFormat;
	}

	public function setInputFormat($format) {
		if(in_array($format,$this->_allowedDataFormats)) {
			$this->_inputFormat = $format;
			return true;
		}
		self::throwException($this->getHelper()->__('Invalid input format, allowed types:').' '.implode(", ",$this->_allowedDataFormats));
	}

	public function getOutputFormat() {
		return $this->_outputFormat;
	}

	public function setOutputFormat($format) {
		if(in_array($format,$this->_allowedDataFormats)) {
			$this->_inputFormat = $format;
			return true;
		}
		self::throwException($this->getHelper()->__('Invalid output format, allowed types:').' '.implode(", ",$this->_allowedDataFormats));
	}

	public static function throwException($msg) {
		throw Mage::exception('GH_Wfirma',$msg);
	}

	public static function log($data) {
		Mage::log($data,null,'ghwfirma_client.log');
	}

	public static function logException($exception) {
		Mage::logException($exception);
	}

	private function doRequest($moduleAction,$post=null) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,
			$this->getApiUrl().$moduleAction."?inputFormat=".$this->getInputFormat()."&outputFormat=".$this->getOutputFormat()
		);
		if(isset($post)) {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($post,JSON_FORCE_OBJECT));
		} else {
			curl_setopt($ch, CURLOPT_POST, 0);
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt($ch, CURLOPT_USERPWD, $this->getLogin() . ':' . $this->getPassword());
		$result = curl_exec($ch);

		if(strpos($moduleAction,'download') === false //means that return is not a pdf file
			&& ($this->getOutputFormat() == 'json' || $this->getOutputFormat() == 'php')) {
			if($this->getOutputFormat() == 'json') {
				$result = json_decode($result, 1);
			} elseif($this->getOutputFormat() == 'php') {
				$result = unserialize($result);
			}

			if($result['status']['code'] !== self::RESULT_STATUS_CODE_OK) {
				$helper = $this->getHelper();
				if($result['status']['code'] == self::RESULT_STATUS_CODE_ERROR) {
					//oznacza że walidacja nie przeszła - nie podano wymaganego pola, albo próbowano zmienić nieedytowalne
					//w takim wypadku logowana jest cała zwrócona tablica, bo są tam dokładne informacje co zostało zrobione źle
					self::log($result);
					self::throwException($helper->__('wFirma api error occured:').' '.$result['status']['code'].'<br/>'.$helper->__('Check logs for more details'));
				} else {
					self::throwException($helper->__('wFirma api error occured:').' '.$result['status']['code']);
				}
			}
		}
		//w innym wypadku wynik to plik xml albo pdf, wiec wiadomo co robic dalej (np $this->downloadInvoice())

		return $result;
	}

	//invoice payment methods
	const INVOICE_PAYMENT_METHOD_CASH = 'cash'; //gotówka
	const INVOICE_PAYMENT_METHOD_TRANSFER = 'transfer'; //przelew
	const INVOICE_PAYMENT_METHOD_COMPENSATION = 'compensation'; //kompensata
	const INVOICE_PAYMENT_METHOD_COD = 'cod'; //pobranie
	const INVOICE_PAYMENT_METHOD_PAYMENT_CARD = 'payment_card'; //karta

	//invoice types (vat payer)
	const INVOICE_TYPE_NORMAL = 'normal'; //faktura vat
	const INVOICE_TYPE_PROFORMA = 'proforma'; //pro forma
	const INVOICE_TYPE_RECEIPT_NORMAL = 'receipt_normal'; //paragon niefiskalny
	const INVOICE_TYPE_RECEIPT_FISCAL_NORMAL = 'receipt_fiscal_normal'; //paragon fiskalny
	const INVOICE_TYPE_INCOME_NORMAL = 'income_normal'; //inny przychód - sprzedaż

	//invoice types (not vat payer)
	const INVOICE_TYPE_BILL = 'bill'; //faktura bez vat
	const INVOICE_TYPE_BILL_PROFORMA = 'proforma_bill'; //pro forma bez vat
	const INVOICE_TYPE_BILL_RECEIPT = 'receipt_bill'; //paragon niefiskalny bez vat
	const INVOICE_TYPE_BILL_RECEIPT_FISCAL = 'receipt_fiscal_bill'; //paragon fiskalny bez vat
	const INVOICE_TYPE_BILL_INCOME = 'income_bill'; //inny przychód - sprzedaż bez vat


	public function addInvoice($post) {
		//dummy data
		/*$postExample = array(
			'invoices' => array(
				'invoice' => array(
					'contractor' => array(
						'id'=>  5241159
					),
					'type'              => self::INVOICE_TYPE_NORMAL, //fvat
					'payment_method'    => self::INVOICE_PAYMENT_METHOD_TRANSFER, //przelew
					'date'              => '2015-10-21',
					'disposaldate'      => '2015-10-21',
					'paymentdate'       => '2015-10-29',
					'price_type'        => 'brutto', //or 'netto' - all goods prices calculations are based on this field
					'id_external'       => 12356,

					'invoicecontents' => array(
						array(
							'invoicecontent' => array(
								'good'  => array(
									'id'    => $this->getItemIdCommission()
								),
								'price' => 500,
								'vat'   => 23, //nie musi tego byc, wtedy wezmie ustawienie z produktu w wFirmie
								'count' => 1 //musi tu byc inaczej jest ustawiane 0
							)
						),
						array(
							'invoicecontent' => array(
								'good'  => array(
									'id'    => $this->getItemIdTransport()
								),
								'price' => 1000,
								'vat'   => 7,
								'count' => 1
							)
						),
						array(
							'invoicecontent' => array(
								'good'  => array(
									'id'    => $this->getItemIdMarketing()
								),
								'price' => 1500,
								'vat'   => 23,
								'count' => 1
							)
						),
						array(
							'invoicecontent' => array(
								'good'  => array(
									'id'    => $this->getItemIdOther()
								),
								'price' => 2000,
								'vat'   => 23,
								'count' => 1
							)
						)
					)
				)
			)
		);*/

		return $this->doRequest("invoices/add",$post);
	}

	public function getGood($goodId) {
		return $this->doRequest("goods/get/$goodId");
	}

	public function getInvoice($invoiceId) {
		return $this->doRequest("invoices/get/$invoiceId");
	}

	public function getInvoiceByNumber($invoiceNumber) {
		$post = array(
			'invoices' => array(
				'parameters' => array(
					'conditions' => array(
						array(
							'field' => 'fullnumber',
							'operator' => 'eq',
							'value' => $invoiceNumber
						)
					)
				)
			)
		);
		return $this->doRequest("invoices/find",$post);
	}

	//invoice download types
	const INVOICE_DOWNLOAD_TYPE_ALL = 'all'; //wydruk oryginału i kopii
	const INVOICE_DOWNLOAD_TYPE_INVOICE = 'invoice'; //wydruk oryginału
	const INVOICE_DOWNLOAD_TYPE_COPY = 'invoicecopy'; //wydruk kopii

	/**
	 * @param Zolago_Payment_Model_Vendor_Invoice $invoiceModel
	 * @param string $type
	 * @param int $address
	 * @param int $leaflet
	 * @param int $duplicate
	 * @throws Zend_Controller_Response_Exception
	 */
	public function downloadInvoice($invoiceModel,$type=self::INVOICE_DOWNLOAD_TYPE_INVOICE,$address=0,$leaflet=0,$duplicate=0) {
		$post = array(
			'invoices' => array(
				'parameters' => array(
					array(
						'parameter' => array(
							'name' => 'page',
							'value' => $type
						)
					),
					array(
						//adres korespondencyjny nabywcy na odwrocie oryginału faktury, umieszczony w takim miejscu,
						//by po złożeniu faktury do rozmiaru DL w Z, adres był na wysokości okienka w kopercie (dozwolone wartości: 0 lub 1)
						'parameter' => array(
							'name' => 'address',
							'value' => $address
						)
					),
					array(
						//druczek przelewu jest generowany tylko dla faktur z metodą płatności "przelew" w walucie PLN. (dozwolone wartości: 0 lub 1)
						'parameter' => array(
							'name' => 'leaflet',
							'value' => $leaflet
						)
					),
					array(
						//w przypadku gdy nasz kontrahent zgubi od nas fakturę, należy przekazać mu duplikat (dozwolone wartości 0 lub 1)
						'parameter' => array(
							'name' => 'duplicate',
							'value' => $duplicate
						)
					),
				)
			)
		);

		$file = $this->doRequest("invoices/download/{$invoiceModel->getData('wfirma_invoice_id')}",$post);
		$filename = "faktura_".preg_replace("/[^a-z0-9\._-]+/i","-",$invoiceModel->getWfirmaInvoiceNumber()).'.pdf';

		Mage::app()->getResponse()
			->setHttpResponseCode(200)
			->setHeader('Pragma', 'public', true)
			->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
			->setHeader ('Content-type', 'application/force-download', true )
			->setHeader('Content-Length', strlen($file)) //size in bytes
			->setHeader('Content-Disposition', 'inline;' . '; filename='.$filename);
		Mage::app()->getResponse()->clearBody();
		Mage::app()->getResponse()->sendHeaders();

		echo $file;
		return;
	}

	public function addContractor($post) {
//		$postExample = array(
//			'contractors' => array(
//				'contractor' => array(
//					//dane firmy
//					'name'          => 'Testowy Contractor', //nazwa długa
//					'altname'       => 'TestContr', //nazwa skrócona
//					'nip'           => 1691732219,
//
//					//adres główny
//					'street'        => 'Plac Defilad 1',
//					'zip'           => '00-901',
//					'city'          => 'Warszawa',
//					'country'       => 'PL', //dwuliterowy kod kraju (http://www.finanse.mf.gov.pl/documents/766655/1198699/KodyKrajow_v3-0.xsd)
//
//					//dane kontaktowe
//					'phone'         => 123123123,
//					'skype'         => 'skypelogin',
//					'fax'           => 123123123,
//					'email'         => 'adam.wilk+wfirma@convertica.pl', //potrzebny do mailowych powiadomien o fakturach
//					'url'           => 'http://www.google.pl',
//					'description'   => 'jakiś tam opis firmy',
//
//					//inne
//					'buyer'         => 1, //Wartość 1 dla oznaczenia, że kontrahent jest nabywcą
//					'seller'        => 0, //Wartość 1 dla oznaczenia, że kontrahent jest dostawcą
//					'account_number'=> '02297745003044811546527422',
//
//					//Domyślna wartość rabatu w procentach, która będzie stosowana dla kontrahenta. Dla rabatu 50% należy wprowadzić wartość 50.
//					//Nie obowiązuje przy produktach oznaczonych jako 'nierabatowalne' ;)
//					'discount_percent'=> 0,
//
//					//płatności
//					'payment_days'  => 7, //domyślny termin płatności
//					'payment_method'=> self::INVOICE_PAYMENT_METHOD_TRANSFER, //domyślna metoda płatności
//
//					//W przypadku wartości 1 i włączonych automatycznych powiadomieniach o niezapłaconych fakturach,
//					//kontrahent otrzyma monit w przypadku braku zapłaty za fakturę.
//					'remind'        => 1,
//
//					//Wartość hasha zabezpieczającego panel klienta (dostępnego przez odsyłacz http://wfirma.pl/invoice_externals/find/HASH).
//					//nie ustawiałem tego - system sam generuje ten hash
//					//'hash'          => 'iisdfiuashdfiuahsiduhfiasudhf',
//
//					//inny adres kontaktowy
//					'different_contact_address' => 0, //czy adres kontaktowy różni się od adresu głównego. 1 - TAK, 0 - NIE
//					/* jezeli 1 wyzej to:
//					'contact_name' => 'Testowy Contractor Kontakt',
//					'contact_street' => 'Plac Defilad 2',
//					'contact_zip' => '00-901',
//					'contact_city' => 'Warszawa',
//					'contact_country' => 'PL', //dwuliterowy kod kraju (http://www.finanse.mf.gov.pl/documents/766655/1198699/KodyKrajow_v3-0.xsd)
//					'contact_person' => 'Osoba kontaktowa'
//					*/
//				)
//			)
//		);

		return $this->doRequest('contractors/add',$post);
	}

	public function editContractor($contractorId,$post) {
		//$post = array as in example above
		return $this->doRequest("contractors/edit/$contractorId",$post);
	}

	/**
	 * @return GH_Wfirma_Helper_Data
	 */
	public function getHelper() {
		if(!$this->_helper) {
			$this->_helper = Mage::helper('ghwfirma');
		}
		return $this->_helper;
	}
}