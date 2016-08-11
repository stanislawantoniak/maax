<?php

class GH_Wfirma_Helper_Data extends Mage_Core_Helper_Abstract {

	protected $_client;
	protected $_tax;

	public function generateInvoice(Zolago_Payment_Model_Vendor_Invoice $invoice) {
		$client = $this->getClient();

		if($invoice->getData('wfirma_invoice_id')) {
			$client::throwException("This invoice has been already generated!");
		}

		$vendor = $invoice->getVendor();
		if($this->updateContractor($vendor)) {
			$invoiceContents = array();

			if(floatval($invoice->getData('commission_brutto'))) {
				$invoiceContents[] = array(
					'invoicecontent' => array(
						'good'  => array(
							'id'    => $client->getItemIdCommission()
						),
						'price' => $invoice->getData('commission_brutto'),
						'vat'   => $this->getTax(), //nie musi tego byc, wtedy wezmie ustawienie z produktu w wFirmie
						'count' => 1 //musi tu byc inaczej jest ustawiane 0
					)
				);
			}

			if(floatval($invoice->getData('transport_brutto'))) {
				$invoiceContents[] = array(
					'invoicecontent' => array(
						'good'  => array(
							'id'    => $client->getItemIdTransport()
						),
						'price' => $invoice->getData('transport_brutto'),
						'vat'   => $this->getTax(), //nie musi tego byc, wtedy wezmie ustawienie z produktu w wFirmie
						'count' => 1 //musi tu byc inaczej jest ustawiane 0
					)
				);
			}

			if(floatval($invoice->getData('marketing_brutto'))) {
				$invoiceContents[] = array(
					'invoicecontent' => array(
						'good'  => array(
							'id'    => $client->getItemIdMarketing()
						),
						'price' => $invoice->getData('marketing_brutto'),
						'vat'   => $this->getTax(), //nie musi tego byc, wtedy wezmie ustawienie z produktu w wFirmie
						'count' => 1 //musi tu byc inaczej jest ustawiane 0
					)
				);
			}

			if(floatval($invoice->getData('other_brutto'))) {
				$invoiceContents[] = array(
					'invoicecontent' => array(
						'good'  => array(
							'id'    => $client->getItemIdOther()
						),
						'price' => $invoice->getData('other_brutto'),
						'vat'   => $this->getTax(), //nie musi tego byc, wtedy wezmie ustawienie z produktu w wFirmie
						'count' => 1 //musi tu byc inaczej jest ustawiane 0
					)
				);
			}

			if(count($invoiceContents)) {
				$post = array(
					'invoices' => array(
						'invoice' => array(
							'contractor' => array(
								'id'=>  $vendor->getData('wfirma_contractor_id')
							),
							'type'              => $client::INVOICE_TYPE_NORMAL, //fvat
							'payment_method'    => $client::INVOICE_PAYMENT_METHOD_COMPENSATION, //kompensata
							'date'              => $invoice->getData('date'),
							'disposaldate'      => $invoice->getData('sale_date'),
							//'paymentdate'       => '2015-10-29',
							'price_type'        => 'brutto', //or 'netto' - all goods prices calculations are based on this field
							'id_external'       => $invoice->getData('vendor_invoice_id'),
							'description'       => $invoice->getData('note'),

							'invoicecontents'   => $invoiceContents
						)
					)
				);

				$result = $client->addInvoice($post);
				if(isset($result['invoices'][0]['invoice'])) {
					$invoiceData = $result['invoices'][0]['invoice'];
					$invoice
						->setData('wfirma_invoice_id',$invoiceData['id'])
						->setData('wfirma_invoice_number',$invoiceData['fullnumber']);

					foreach($invoiceData['invoicecontents'] as $invoiceItem) {
						switch($invoiceItem['invoicecontent']['good']['id']) {
							case $client->getItemIdCommission():
								$invoice->setData('commission_netto',$invoiceItem['invoicecontent']['netto']);
								break;

							case $client->getItemIdTransport():
								$invoice->setData('transport_netto',$invoiceItem['invoicecontent']['netto']);
								break;

							case $client->getItemIdMarketing():
								$invoice->setData('marketing_netto',$invoiceItem['invoicecontent']['netto']);
								break;

							case $client->getItemIdOther():
								$invoice->setData('other_netto',$invoiceItem['invoicecontent']['netto']);
								break;
						}
					}
					$invoice->save();

					return true;
				} else {
					$client::throwException("Something went wrong while generating wFirma invoice");
				}
			} else {
				$client::throwException("Invoice does not contain any items");
			}
		} else {
			$client::throwException("Could not update vendor contractor data, invoice generation aborted");
		}

		return false;
	}


	public function updateContractor(Zolago_Dropship_Model_Vendor $vendor) {
		$client = $this->getClient();

		$post = array(
			'contractors' => array(
				'contractor' => array(
					//dane firmy
					'name'          => $vendor->getData('company_name'), //nazwa długa
					//'altname'       => 'TestContr', //nazwa skrócona
					'tax_id_type'   => 'nip',//Rodzaj identyfikatora podatkowego. Dopuszczalne wartości nip, vat, pesel, regon, custom, none.
					'nip'           => $vendor->getData('tax_no'),

					//adres główny
					'street'        => $vendor->getData('billing_street'),
					'zip'           => $vendor->getData('billing_zip'),
					'city'          => $vendor->getData('billing_city'),
					'country'       => 'PL', //dwuliterowy kod kraju (http://www.finanse.mf.gov.pl/documents/766655/1198699/KodyKrajow_v3-0.xsd)

					//dane kontaktowe
					'phone'         => $vendor->getData('executive_telephone') ? $vendor->getData('executive_telephone') : $vendor->getData('executive_telephone_mobile') ? $vendor->getData('executive_telephone_mobile') : '',
					//'skype'         => 'skypelogin',
					//'fax'           => 123123123,
					'email'         => $vendor->getData('billing_email'), //potrzebny do mailowych powiadomien o fakturach
					'url'           => $vendor->getData('www'),
					//'description'   => 'jakiś tam opis firmy',

					//inne
					/*'buyer'         => 1, //Wartość 1 dla oznaczenia, że kontrahent jest nabywcą
					'seller'        => 0, //Wartość 1 dla oznaczenia, że kontrahent jest dostawcą
					'account_number'=> '02297745003044811546527422',*/

					//Domyślna wartość rabatu w procentach, która będzie stosowana dla kontrahenta. Dla rabatu 50% należy wprowadzić wartość 50.
					//Nie obowiązuje przy produktach oznaczonych jako 'nierabatowalne' ;)
					//'discount_percent'=> 0,

					//płatności
					//'payment_days'  => 7, //domyślny termin płatności
					'payment_method'=> GH_Wfirma_Model_Client::INVOICE_PAYMENT_METHOD_COMPENSATION, //domyślna metoda płatności - kompensata

					//W przypadku wartości 1 i włączonych automatycznych powiadomieniach o niezapłaconych fakturach,
					//kontrahent otrzyma monit w przypadku braku zapłaty za fakturę.
					//'remind'        => 1,

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
					*/
					'contact_person' => $vendor->getData('executive_firstname')." ".$vendor->getData('executive_lastname')
				)
			)
		);

		if ($vendor->getData('wfirma_contractor_id')) {
			//try to update
			try {
				$return = $client->editContractor($vendor->getData('wfirma_contractor_id'), $post);
				return true;
			} catch(GH_Wfirma_Exception $e) {
				if($e->getMessage() == GH_Wfirma_Model_Client::RESULT_STATUS_CODE_NOT_FOUND) {
					//possible that someone deleted contractor in wFirma, if so add new one
					$vendor->setData('wfirma_contractor_id', 0);
				}
			}
		}

		//not on else because 'wfirma_contractor_id' can change in previous if
		if(!$vendor->getData('wfirma_contractor_id')) {
			//add
			$return = $client->addContractor($post);

			if(isset($return['contractors'][0]['contractor']['id'])) {
				$vendor->setData('wfirma_contractor_id',$return['contractors'][0]['contractor']['id'])->save();
				return true;
			} else {
				$client::throwException("Something went wrong while adding vendor as wFirma contractor");
			}
		}

		return false;
	}

	/**
	 * @return GH_Wfirma_Model_Client
	 */
	public function getClient() {
		if(!$this->_client) {
			$this->_client = Mage::getSingleton('ghwfirma/client');
		}
		return $this->_client;
	}

	public function getTax() {
		if(!$this->_tax) {
			$tax = Mage::helper('ghstatements')->getTax();
			$this->_tax = ($tax * 100) - 100; //need percents so: 1.23 will be returned as 23
		}
		return $this->_tax;
	}

    /**
     * Get url for download invoice
     *
     * @param int $id
     * @return string
     */
    public function getVendorInvoiceUrl($id) {
        return Mage::getUrl('*/*/download',array('id' => $id));
    }
    
    /**
     * dowload pdf document from wfirma
     *
     * @param Zolago_Dropship_Model_Vendor $vendor
     * @param int $id
     */
    public function getVendorInvoice($vendor, $id) {
            
            /** @var Zolago_Payment_Model_Vendor_Invoice $model */
            $model = Mage::getModel("zolagopayment/vendor_invoice")->load($id);
            if ($vendor && $vendor->getId()) {
                if ($vendor->getId() != $model->getVendorId()) {
                    Mage::throwException('Vendor not allowed to get this invoice');
                }
            }
            if (!$model->getId()) {
                Mage::throwException("Vendor Invoice not found");
            } elseif(!$model->getData('wfirma_invoice_id')) {
                Mage::throwException("Invoice has not been generated");
            } else {
                $this->getClient()->downloadInvoice($model);
            }
    }
}