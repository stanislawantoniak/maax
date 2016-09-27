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
     * @param $value
     * @return string
     */
    public function formatToDocNumber($value)
    {
        //Format liczb    : bez separatora tysiêcy, separator dziesiêtny "."
        $decPoint = '.';
        $thousandsSep = '';
        return number_format($value, 2, $decPoint, $thousandsSep);
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

    public function shippingMethodDescription($code)
    {
        return isset($this->shippingMethodsDescription()[$code]) ? $this->shippingMethodsDescription()[$code] : $this->shippingMethodsDescription()[Zolago_Po_Model_Po::GH_API_DELIVERY_METHOD_STANDARD_COURIER];
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

        return $this->formatToDocNumber($discountPercent);
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
        return implode(' ',
            array(
                $deliveryAddress->delivery_zip_code,
                $deliveryAddress->delivery_city,
                $deliveryAddress->delivery_street,
            )
        );
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


    public function addOrders($params)
    {
        $orders = [
            [
                $params['customer_email'],                                      //(A)DKONTRAH        : String; - identyfikator kontrahenta (numer) (15)
                $params['order_date'],                                          //(B)DATA            : TDateTime; - Data dokumentu
                self::ORDER_DOC_NAME_ZA,                                        //(C)NAZWADOK        : String; - nazwa dokumentu (10)  opis dozwolonych wartośći w pkt 7.
                $params['order_id'],                                            //(D)NRDOK           : String; - numer dokumentu (25)
                '',                                                             //(E)TERMIN          : TDateTime; - termin płatności (??????????)
                $this->paymentMethodDescription($params['payment_method']),     //(F)PLATNOSC        : String; - sposób płatności (35)
                $this->formatToDocNumber($params['order_total']),               //(G)SUMA            : Currency; - Wartość brutto dokumentu - liczone zgodnie z definicją dokumentu
                count($params['order_items']),                                  //(H)ILEPOZ          : Integer; - ilość pozycji
                0,                                                              //(I)GOTOWKA         : Currency; - zapłata gotówkowa przyjęta na dokumencie (??????????)
                '',                                                             //(J)DOT_DATA        : TDateTime; - data dokumentu powiązanego (np KP do FA) (??????????)
                self::ORDER_DOC_NAME_PAR,                                       //(K)DOT_NAZWADOK    : String; - nazwa dokumentu powiązanego (10) (np KP do FA) (??????????)
                '',                                                             //(L)DOT_NRDOK       : String; - numer dokumentu powiązanego (25) (np KP do FA)
                '',                                                             //(M)ANULOWANY       : Boolean; - czy dokument został anulowany
                $this->_generateCustomerFLName($params),                        //(N)UWAGI           : String; - (Memo) Uwagi do dokumentu (??????????)
                $this->_generateCustomerDeliveryAddress($params),               //(O)NRZLEC          : String; - numer zlecenia (20)
                $this->_generateCustomerInvoiceAddress($params),                //(P)CECHA_1         : String; - Cecha 1 (35)
                $this->paymentMethodDescription($params['payment_method']),     //(Q)CECHA_2         : String; - Cecha 2 (35)
                $this->shippingMethodDescription($params['delivery_method']),   //(R)CECHA_3         : String; - Cecha 3 (35)
                '',                                                             //(S)IDKONTRAHODB    : String; - identyfikator kontrahenta odbierającego dokument (numer) (15)
                '',                                                             //(T)DATAOBOW        : TDateTime; - data obowiązywania zamówienia
                '',                                                             //(U)CECHA_4         : String; - Cecha 4 (35)
                '',                                                             //(V)CECHA_5         : String; - Cecha 5 (35)
                '',                                                             //(W)IDKONTRAHDOST   : String; - identyfikator kontrahenta dostawcy - (numer) (15);
                '',                                                             //(X)MAGAZYN         : String; - numer magazynu
                '',                                                             //(Y)WYDRUKOWANY     : Boolean;- czy dokument był drukowany na zwykłej drukarce;
                '',                                                             //(Z)ZAFISKALIZOWANY : Boolean; - czy dokument był zafiskalizowany - wydrukowany na drukarce fiskalnej (parametr brany pod uwagę tylko dla dokum. podlegających fiskalizacji);
            ]
        ];

        $deliveryAddress = $params['delivery_data']->delivery_address;

        $invoiceData = $params['invoice_data'];
        $invoiceRequired = (bool)$invoiceData->invoice_required;

        $nip = ($invoiceRequired) ? $invoiceData->invoice_address->invoice_nip : '';

        $orderCustomerLine = [
            [
                '',                                                             //(A)IDKONTRAH w kontrahent.txt jako pusta kolumna
                $params['customer_email'],                                      //(B)NAZWASKR w kontrahent.txt jako adresu e-mail płatnika (ten sam co w dok.txt)
                $this->_generateCustomerFLName($params),                        //(C)NAZWADL w kontrahent (to już dane płatnika konta jeśli potrzeba to oddzielone ";"
                $nip,                                                           //(D)NIP
                $deliveryAddress->delivery_city,                                //(E)MIEJSCOWOSC
                $deliveryAddress->delivery_zip_code,                            //(F)KODPOCZTA
                $deliveryAddress->delivery_city,                                //(G)POCZTA
                $deliveryAddress->delivery_street,                              //(H)ULICA
                '',                                                             //(I)NRDOMU
                '',                                                             //(J)NRLOKALU
                '',                                                             //(K)puste
                '',                                                             //(L)puste
                2,                                                              //(M)2
            ]
        ];


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
                    $this->formatToDocNumber($orderItem['item_value_after_discount']),                                  //CENA          : Currency; - cena netto przed bonifikatą
                    'N',                                                                                                //PROCBONIF     : Currency; - bonifikata - liczone zgodnie z definicją dokumentu
                    'N',                                                                                                //CENA_UZG      : Boolean; - czy cena jest uzgodniona
                    'T',                                                                                                //CENA_BRUTTO   : Boolean; - czy cena jest od brutto (domyślnie FALSE)
                    $orderItem['item_name'] . ' ' . $params['delivery_data']->delivery_point_name,                                                                            //Uwagi         : String; - uwagi;

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
                    $this->formatToDocNumber($orderItem['item_value_before_discount'] / (int)$orderItem['item_qty']),   //CENA          : Currency; - cena netto przed bonifikatą
                    $this->getDiscountPercent($orderItem),                                                              //PROCBONIF     : Currency; - bonifikata - liczone zgodnie z definicją dokumentu
                    'N',                                                                                                //CENA_UZG      : Boolean; - czy cena jest uzgodniona
                    'T',                                                                                                //CENA_BRUTTO   : Boolean; - czy cena jest od brutto (domyślnie FALSE)
                    $orderItem['item_name'],                                                                            //Uwagi         : String; - uwagi;

                    '',                                                                                                 //Cecha_1       : String; - wartość dla cechy 1;
                    '',                                                                                                 //Cecha_2       : String; - wartość dla cechy 1;
                    '',                                                                                                 //Cecha_3       : String; - wartość dla cechy 1;

                    '',                                                                                                 //MAG_OZNNRWYDR : String; - z jakiego magazynu realizować daną pozycję (jako identyfikator podać pole "Oznaczenie na wygruku" magazynu);
                    '',                                                                                                 //STAWKAVATIDENT: String; - wymuszona stawka VAT dla pozycji, gdny nie wystepuje to pobierana jest z kartoteki na datę dokumentu;
                );

            }

            array_push($orderItemsLine, $itemData);
        }
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