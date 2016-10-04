<?php


class ZolagoOs_OrdersExport_Model_Export_Order
    extends ZolagoOs_OrdersExport_Model_Export_Abstract
{
    const ORDER_DOC_NAME_FA = 'FA';             //faktury sprzedaży
    const ORDER_DOC_NAME_WZ = 'WZ';             //dokumenty Wydane zewnętrzne
    const ORDER_DOC_NAME_PAR = 'PAR';           //paragony
    const ORDER_DOC_NAME_KP = 'KP';             //dokumenty KP
    const ORDER_DOC_NAME_KW = 'KW';             //dokument KW-Kontrahent-Rozliczenie
    const ORDER_DOC_NAME_KW_ROZ = 'KW_ROZ';     //dokument KW-Rozliczenie
    const ORDER_DOC_NAME_NOP = 'NOP';           //dokumenty opakowań
    const ORDER_DOC_NAME_ZA = 'ZA';             //zamówienia od odbiorców
    const ORDER_DOC_NAME_MM_PLUS = 'MM+';       //dokument MM+
    const ORDER_DOC_NAME_MM_MINUS = 'MM-';      //dokument MM-
    const ORDER_DOC_NAME_ZAWEW = 'ZAWEW';       //dokumenty zamówień wewnętrznych
    const ORDER_DOC_NAME_RW = 'RW';             //dokumenty rozchodu wewnętrznego
    //const ORDER_DOC_NAME_XXX = 'XXX';           //mozna podać dowolny skrót definicji dokumentu w obrębie rodzajów wymienionych wyżej.


    const ORDER_DELIVERY_CODE_KURIER_POBRANIE = 'WYS3';
    const ORDER_DELIVERY_CODE_KURIER_PRZELEW = 'WYS4';
    const ORDER_DELIVERY_CODE_PACZKA_W_RUCHU = 'WYS9';
    const ORDER_DELIVERY_CODE_PACZKOMAT_INPOST = 'WYS7';


    const ORDEREXPORT_DELIMETR_ORDERINFO = "^";


    public function getDirectoryPath()
    {
        $directory = $this->getHelper()->getExportDirectory();
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        return $directory;
    }

    public function getOrdersFileName()
    {
        return $this->getDirectoryPath() . DS . 'dok.txt';
    }

    public function getOrderItemsFileName()
    {
        return $this->getDirectoryPath() . DS . 'poz.txt';
    }

    public function getOrderCustomerFileName()
    {
        return $this->getDirectoryPath() . DS . 'kontrahent.txt';
    }


    /**
     * @return array
     */
    public function paymentMethodsDescription()
    {
        return array(
            Zolago_Po_Model_Po::GH_API_PAYMENT_METHOD_CC => 'Płatność online (Dotpay)',
            Zolago_Po_Model_Po::GH_API_PAYMENT_METHOD_ONLINE_BT => 'Płatność online (Dotpay)',
            Zolago_Po_Model_Po::GH_API_PAYMENT_METHOD_BT => 'Przelew bankowy',
            Zolago_Po_Model_Po::GH_API_PAYMENT_METHOD_COD => 'Płatność za pobraniem',
        );
    }

    public function shippingMethodsDescription()
    {
        return array(
            Zolago_Po_Model_Po::GH_API_DELIVERY_METHOD_STANDARD_COURIER => 'Kurier Standard',
            Zolago_Po_Model_Po::GH_API_DELIVERY_METHOD_INPOST_LOCKER => 'Paczkomaty InPost',
            Zolago_Po_Model_Po::GH_API_DELIVERY_METHOD_POLISH_POST => 'Poczta Polska',
            Zolago_Po_Model_Po::GH_API_DELIVERY_METHOD_PWR_LOCKER => 'Paczka w Ruchu',
        );
    }

    /**
     * @param $code
     * @return mixed
     */
    public function paymentMethodDescription($code)
    {
        return isset($this->paymentMethodsDescription()[$code]) ? $this->paymentMethodsDescription()[$code] : $code;
    }

    /**
     * Get delivery method codes require point name
     * @return array
     */
    public function getDeliveryMethodsRequiredDeliveryPointName()
    {
        return [Zolago_Po_Model_Po::GH_API_DELIVERY_METHOD_INPOST_LOCKER, Zolago_Po_Model_Po::GH_API_DELIVERY_METHOD_PWR_LOCKER];
    }

    public function shippingMethodDescription($params)
    {
        $code = $params['delivery_method'];
        $description = $this->shippingMethodsDescription()[Zolago_Po_Model_Po::GH_API_DELIVERY_METHOD_STANDARD_COURIER];
        if (isset($this->shippingMethodsDescription()[$code])) {
            $description = "Delivery Method:" . $this->shippingMethodsDescription()[$code];
        }

        if (in_array($code, $this->getDeliveryMethodsRequiredDeliveryPointName())) {
            switch ($code) {
                case Zolago_Po_Model_Po::GH_API_DELIVERY_METHOD_INPOST_LOCKER:
                    $description .= self::ORDEREXPORT_DELIMETR_ORDERINFO . 'Symbol paczkomatu InPost:' . $params['delivery_data']->delivery_point_name;
                    break;
                case Zolago_Po_Model_Po::GH_API_DELIVERY_METHOD_PWR_LOCKER:
                    $description .= self::ORDEREXPORT_DELIMETR_ORDERINFO . 'Paczka w Ruchu punkt ID:' . $params['delivery_data']->delivery_point_name;
                    break;

            }
        }

        return $description;
    }

    /**
     *
     * >> ### WYSYŁKA
     * >> Dodatkowo do każdego pliku poz.txt powinniśmy dodawać dodatkową pozycję
     * >> którą jest usługa wysyłki w zależności od wybranej w systemie, ich kody
     * >> są następujące:
     * >> WYS3    KURIER POBRANIE
     * >> WYS4    KURIER PRZELEW
     * >> WYS9    PACZKA W RUCHU
     * >> WYS7    PACZKOMAT INPOST
     *
     * @param $deliveryMethod
     * @param $paymentMethod
     * @return string
     */
    public function shippingMethodCode($deliveryMethod, $paymentMethod)
    {
        switch ($deliveryMethod) {
            case Zolago_Po_Model_Po::GH_API_DELIVERY_METHOD_INPOST_LOCKER:
                $code = self::ORDER_DELIVERY_CODE_PACZKOMAT_INPOST;
                break;
            case Zolago_Po_Model_Po::GH_API_DELIVERY_METHOD_PWR_LOCKER:
                $code = self::ORDER_DELIVERY_CODE_PACZKA_W_RUCHU;
                break;
            case Zolago_Po_Model_Po::GH_API_DELIVERY_METHOD_STANDARD_COURIER:
            case Zolago_Po_Model_Po::GH_API_DELIVERY_METHOD_POLISH_POST:
                if ($paymentMethod == Zolago_Po_Model_Po::GH_API_PAYMENT_METHOD_COD) {
                    $code = self::ORDER_DELIVERY_CODE_KURIER_POBRANIE;
                } else {
                    $code = self::ORDER_DELIVERY_CODE_KURIER_PRZELEW;
                }
                break;
            default:
                if ($paymentMethod == Zolago_Po_Model_Po::GH_API_PAYMENT_METHOD_COD) {
                    $code = self::ORDER_DELIVERY_CODE_KURIER_POBRANIE;
                } else {
                    $code = self::ORDER_DELIVERY_CODE_KURIER_PRZELEW;
                }
        }

        return $code;
    }

    /**
     * @param $orderItem
     * @return string
     */
    public function getDiscountPercent($orderItem)
    {
        if ((int)$orderItem['item_discount'] == 0)
            return 'N';

        $discountPercent = ($orderItem['item_discount'] * 100) / $orderItem['item_value_before_discount'];

        return $this->getHelper()->formatToDocNumber($discountPercent);
    }


    /**
     * Generate customer data for kontrahent.txt (column NAZWADL)
     * @param $params
     * @return string
     */
    private function _generateCustomerData($params)
    {

        $deliveryAddress = $params['delivery_data']->delivery_address;

        $info = array(
            $deliveryAddress->delivery_first_name . ' ' . $deliveryAddress->delivery_last_name
        );


        return implode(self::ORDEREXPORT_DELIMETR_ORDERINFO, $info);
    }

    /**
     * Generate customer name: first name last name
     * @param $params
     * @return string
     */
    private function _generateCustomerFLName($params)
    {
        return implode(' ',
            array(
                $params['delivery_data']->delivery_address->delivery_first_name,
                $params['delivery_data']->delivery_address->delivery_last_name
            )
        );
    }

    /**
     *
     * Generate customer address: postcode city street
     *
     * @param $params
     * @return string
     */
    private function _generateCustomerDeliveryAddress($params)
    {

        $deliveryAddress = $params['delivery_data']->delivery_address;
        $result = array(
            $deliveryAddress->delivery_zip_code,
            $deliveryAddress->delivery_city,
            $deliveryAddress->delivery_street,
        );

        return implode(' ', $result);
    }

    private function _generateCustomerInvoiceAddress($params)
    {
        $invoiceData = $params['invoice_data'];
        $invoiceRequired = (bool)$invoiceData->invoice_required;

        if (!$invoiceRequired)
            return '';

        return implode(' ',
            array(
                $invoiceData->invoice_address->invoice_zip_code,
                $invoiceData->invoice_address->invoice_city,
                $invoiceData->invoice_address->invoice_street
            )
        );
    }

    private function _getOrderDetails($params)
    {
        $result = [];

        $result[] = $this->_generateCustomerFLName($params);

        $deliveryAddress = $params['delivery_data']->delivery_address;

        //Company
        if (!empty($deliveryAddress->delivery_company_name))
            $result[] = 'Firma: ' . $deliveryAddress->delivery_company_name;

        //NIP
        $result[] = "Delivery Data:" . $this->_generateCustomerDeliveryAddress($params);
        $result[] = "Telephone:" . $deliveryAddress->phone;

        $invoiceAddress = $this->_generateCustomerInvoiceAddress($params);

        //if (!empty($invoiceAddress)) {
        $result[] = "Invoice Data:" . $this->_generateCustomerInvoiceAddress($params);
        //}

        $result[] = 'Payment Data:' . $this->paymentMethodDescription($params['payment_method']);


        //Delivery point name
        $result[] = $this->shippingMethodDescription($params);


        return implode(self::ORDEREXPORT_DELIMETR_ORDERINFO, $result);
    }


    private function _createOrderLine($params)
    {

        $invoiceData = $params['invoice_data'];
        $invoiceRequired = (bool)$invoiceData->invoice_required;

        $orders = [
            [

                //(A)DKONTRAH        : String; - identyfikator kontrahenta (numer) (15)
                $this->getHelper()->toWindows1250($params['customer_email']),

                //(B)DATA            : TDateTime; - Data dokumentu
                $params['order_date'],

                //(C)NAZWADOK        : String; - nazwa dokumentu (10)  opis dozwolonych wartośći w pkt 7.
                $this->getHelper()->toWindows1250(self::ORDER_DOC_NAME_ZA),

                //(D)NRDOK           : String; - numer dokumentu (25)
                $params['order_id'],

                //(E)TERMIN          : TDateTime; - termin płatności
                '',

                //(F)PLATNOSC        : String; - sposób płatności (35)
                $this->getHelper()->toWindows1250($this->paymentMethodDescription($params['payment_method'])),

                //(G)SUMA            : Currency; - Wartość brutto dokumentu - liczone zgodnie z definicją dokumentu
                $this->getHelper()->formatToDocNumber($params['order_total']),

                //(H)ILEPOZ          : Integer; - ilość pozycji
                count($params['order_items']),

                //(I)GOTOWKA         : Currency; - zapłata gotówkowa przyjęta na dokumencie
                0,

                //(J)DOT_DATA        : TDateTime; - data dokumentu powiązanego (np KP do FA)
                '',

                //(K)DOT_NAZWADOK    : String; - nazwa dokumentu powiązanego (10) (np KP do FA)
                ($invoiceRequired) ? self::ORDER_DOC_NAME_FA : self::ORDER_DOC_NAME_PAR,

                //(L)DOT_NRDOK       : String; - numer dokumentu powiązanego (25) (np KP do FA)
                '',

                //(M)ANULOWANY       : Boolean; - czy dokument został anulowany
                '',

                //(N)UWAGI           : String; - (Memo) Uwagi do dokumentu
                $this->getHelper()->toWindows1250($this->_getOrderDetails($params)),


                '', //(O)NRZLEC          : String; - numer zlecenia (20)
                '', //(P)CECHA_1         : String; - Cecha 1 (35)
                '', //(Q)CECHA_2         : String; - Cecha 2 (35)
                '', //(R)CECHA_3         : String; - Cecha 3 (35)
                '', //(S)IDKONTRAHODB    : String; - identyfikator kontrahenta odbierającego dokument (numer) (15)
                '', //(T)DATAOBOW        : TDateTime; - data obowiązywania zamówienia
                '', //(U)CECHA_4         : String; - Cecha 4 (35)
                '', //(V)CECHA_5         : String; - Cecha 5 (35)
                '', //(W)IDKONTRAHDOST   : String; - identyfikator kontrahenta dostawcy - (numer) (15);
                '', //(X)MAGAZYN         : String; - numer magazynu
                '', //(Y)WYDRUKOWANY     : Boolean;- czy dokument był drukowany na zwykłej drukarce;
                '', //(Z)ZAFISKALIZOWANY : Boolean; - czy dokument był zafiskalizowany - wydrukowany na drukarce fiskalnej (parametr brany pod uwagę tylko dla dokum. podlegających fiskalizacji);
            ]
        ];

        return $orders;
    }

    private function _createCustomerLine($params)
    {
        $invoiceData = $params['invoice_data'];
        $invoiceRequired = (bool)$invoiceData->invoice_required;
        $nip = ($invoiceRequired) ? $invoiceData->invoice_address->invoice_tax_id : '';
        $deliveryAddress = $params['delivery_data']->delivery_address;
        $orderCustomerLine = [
            [
                '',                                                             //(A)IDKONTRAH w kontrahent.txt jako pusta kolumna
                $this->getHelper()->toWindows1250($params['customer_email']),                                      //(B)NAZWASKR w kontrahent.txt jako adresu e-mail płatnika (ten sam co w dok.txt)
                $this->getHelper()->toWindows1250($this->_generateCustomerData($params)),                          //(C)NAZWADL w kontrahent (to już dane płatnika konta jeśli potrzeba to oddzielone ";"
                $nip,                                                           //(D)NIP
                $this->getHelper()->toWindows1250($deliveryAddress->delivery_city),                                //(E)MIEJSCOWOSC
                $deliveryAddress->delivery_zip_code,                            //(F)KODPOCZTA
                $this->getHelper()->toWindows1250($deliveryAddress->delivery_city),                                //(G)POCZTA
                $this->getHelper()->toWindows1250($deliveryAddress->delivery_street),                              //(H)ULICA
                '',                                                             //(I)NRDOMU
                '',                                                             //(J)NRLOKALU
                '',                                                             //(K)puste
                '',                                                             //(L)puste
                2,                                                              //(M)2
            ]
        ];

        return $orderCustomerLine;
    }

    private function _createOrderItemsLine($params)
    {
        $orderItems = $params["order_items"];

        $orderItemsLine = [];
        foreach ($orderItems as $orderItem) {
            $orderItem = (array)$orderItem;

            if ((int)$orderItem['is_delivery_item'] == 1) {
                /**
                 * >> ### WYSYŁKA
                 * >> Dodatkowo do każdego pliku poz.txt powinniśmy dodawać dodatkową pozycję
                 * >> którą jest usługa wysyłki
                 */
                $itemData = array(
                    self::ORDER_DOC_NAME_ZA,                                                                            //NAZWADOK      : String; - nazwa dokumentu (10)
                    $params['order_id'],                                                                                //NRDOK         : String; - numer dokumentu (25)
                    $this->shippingMethodCode($params['delivery_method'], $params['payment_method']),                                                                  //KODTOW        : String; - indeks dokumentu (25)
                    1,                                                                                                  //ILOSC         : Currency; - ilość
                    $this->getHelper()->formatToDocNumber($orderItem['item_value_after_discount']),                                  //CENA          : Currency; - cena netto przed bonifikatą
                    'N',                                                                                                //PROCBONIF     : Currency; - bonifikata - liczone zgodnie z definicją dokumentu
                    'N',                                                                                                //CENA_UZG      : Boolean; - czy cena jest uzgodniona
                    'T',                                                                                                //CENA_BRUTTO   : Boolean; - czy cena jest od brutto (domyślnie FALSE)
                    $this->getHelper()->toWindows1250($orderItem['item_name']),                                                                            //Uwagi         : String; - uwagi;

                    '',                                                                                                 //Cecha_1       : String; - wartość dla cechy 1;
                    '',                                                                                                 //Cecha_2       : String; - wartość dla cechy 1;
                    '',                                                                                                 //Cecha_3       : String; - wartość dla cechy 1;

                    '',                                                                                                 //MAG_OZNNRWYDR : String; - z jakiego magazynu realizować daną pozycję (jako identyfikator podać pole "Oznaczenie na wygruku" magazynu);
                    '',                                                                                                 //STAWKAVATIDENT: String; - wymuszona stawka VAT dla pozycji, gdny nie wystepuje to pobierana jest z kartoteki na datę dokumentu;
                );
            } else {

                $itemData = array(
                    self::ORDER_DOC_NAME_ZA,                                                                            //NAZWADOK      : String; - nazwa dokumentu (10)
                    $params['order_id'],                                                                                //NRDOK         : String; - numer dokumentu (25)
                    $orderItem['item_sku'],                                                                             //KODTOW        : String; - indeks dokumentu (25)
                    (int)$orderItem['item_qty'],                                                                        //ILOSC         : Currency; - ilość
                    $this->getHelper()->formatToDocNumber($orderItem['item_value_before_discount'] / (int)$orderItem['item_qty']),   //CENA          : Currency; - cena netto przed bonifikatą
                    $this->getDiscountPercent($orderItem),                                                              //PROCBONIF     : Currency; - bonifikata - liczone zgodnie z definicją dokumentu
                    'N',                                                                                                //CENA_UZG      : Boolean; - czy cena jest uzgodniona
                    'T',                                                                                                //CENA_BRUTTO   : Boolean; - czy cena jest od brutto (domyślnie FALSE)
                    '',                                                                                                 //Uwagi         : String; - uwagi;
                    '',                                                                                                 //Cecha_1       : String; - wartość dla cechy 1;
                    '',                                                                                                 //Cecha_2       : String; - wartość dla cechy 1;
                    '',                                                                                                 //Cecha_3       : String; - wartość dla cechy 1;
                    '',                                                                                                 //MAG_OZNNRWYDR : String; - z jakiego magazynu realizować daną pozycję (jako identyfikator podać pole "Oznaczenie na wygruku" magazynu);
                    '',                                                                                                 //STAWKAVATIDENT: String; - wymuszona stawka VAT dla pozycji, gdny nie wystepuje to pobierana jest z kartoteki na datę dokumentu;
                );

            }

            array_push($orderItemsLine, $itemData);
        }

        return $orderItemsLine;
    }


    public function addOrders($params)
    {
        //Collect data
        $orders = $this->_createOrderLine($params);
        $orderCustomerLine = $this->_createCustomerLine($params);
        $orderItemsLine = $this->_createOrderItemsLine($params);

        //Write to file
        try {
            $this->_pushLinesToFiles($orders, $orderItemsLine, $orderCustomerLine);

            $message = 'ok';
            $status = TRUE;
        } catch (Exception $e) {
            $message = $e->getMessage();
            $status = FALSE;
        }


        $obj = new StdClass();
        $obj->message = $message;
        $obj->status = $status;
        return $obj;
    }


    /**
     * @param $orders
     * @param $orderItemsLine
     * @param $orderCustomerLine
     * @return bool
     */
    private function _pushLinesToFiles($orders, $orderItemsLine, $orderCustomerLine)
    {
        try {
            $this->pushOrderLineToFile($orders);
            $this->pushOrderItemsLineToFile($orderItemsLine);
            $this->pushOrderCustomerFile($orderCustomerLine);

            $response = TRUE;
        } catch (Exception $e) {
            $response = FALSE;
        }

        return $response;
    }

    /**
     * @param $line
     */
    public function pushOrderLineToFile($line)
    {
        $fileName = $this->getOrdersFileName();
        $this->pushLinesToFile($fileName, $line);
    }


    /**
     * @param $line
     */
    public function pushOrderItemsLineToFile($line)
    {
        $fileName = $this->getOrderItemsFileName();
        $this->pushLinesToFile($fileName, $line);
    }

    /**
     * @param $line
     */
    public function pushOrderCustomerFile($line)
    {
        $fileName = $this->getOrderCustomerFileName();
        $this->pushLinesToFile($fileName, $line);
    }

}