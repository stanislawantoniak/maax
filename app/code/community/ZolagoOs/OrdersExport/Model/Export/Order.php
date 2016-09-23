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
    const ORDER_DOC_NAME_XXX = 'XXX';           //mozna podać dowolny skrót definicji dokumentu w obrębie rodzajów wymienionych wyżej.


    public function getFileName()
    {
        $directory = $this->getHelper()->getExportDirectory();
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        return $directory . DS . 'dok.txt';
    }

    /**
     * @param $value
     * @return string
     */
    public function formatToDocNumber($value)
    {
        return number_format($value, 2, '.', '');
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

    /**
     * @param $code
     * @return mixed
     */
    public function paymentMethodDescription($code)
    {
        return isset($this->paymentMethodsDescription()[$code]) ? $this->paymentMethodsDescription()[$code] : $code;
    }

    public function addOrders($params)
    {
        //krumo($params);

        $line = array(
            array(
                $params['customer_email'],                                  //(A)DKONTRAH        : String; - identyfikator kontrahenta (numer) (15)
                $params['order_date'],                                      //(B)DATA            : TDateTime; - Data dokumentu
                self::ORDER_DOC_NAME_ZA,                                    //(C)NAZWADOK        : String; - nazwa dokumentu (10)  opis dozwolonych wartośći w pkt 7.
                $params['order_id'],                                        //(D)NRDOK           : String; - numer dokumentu (25)
                '',                                                         //(E)TERMIN          : TDateTime; - termin płatności (??????????)
                $this->paymentMethodDescription($params['payment_method']), //(F)PLATNOSC        : String; - sposób płatności (35)
                $this->formatToDocNumber($params['order_total']),           //(G)SUMA            : Currency; - Wartość brutto dokumentu - liczone zgodnie z definicją dokumentu
                count($params['order_items']),                              //(H)ILEPOZ          : Integer; - ilość pozycji
                0,                                                          //(I)GOTOWKA         : Currency; - zapłata gotówkowa przyjęta na dokumencie (??????????)
                '',                                                         //(J)DOT_DATA        : TDateTime; - data dokumentu powiązanego (np KP do FA) (??????????)
//(K)DOT_NAZWADOK    : String; - nazwa dokumentu powiązanego (10) (np KP do FA)
//(L)DOT_NRDOK       : String; - numer dokumentu powiązanego (25) (np KP do FA)
//(M)ANULOWANY       : Boolean; - czy dokument został anulowany
//(N)UWAGI           : String; - (Memo) Uwagi do dokumentu
//(O)NRZLEC          : String; - numer zlecenia (20)
//(P)CECHA_1         : String; - Cecha 1 (35)
//(Q)CECHA_2         : String; - Cecha 2 (35)
//(R)CECHA_3         : String; - Cecha 3 (35)
//(S)IDKONTRAHODB    : String; - identyfikator kontrahenta odbierającego dokument (numer) (15)
//(T)DATAOBOW        : TDateTime; - data obowiązywania zamówienia
//(U)CECHA_4         : String; - Cecha 4 (35)
//(V)CECHA_5         : String; - Cecha 5 (35)
//(W)IDKONTRAHDOST   : String; - identyfikator kontrahenta dostawcy - (numer) (15);
//(X)MAGAZYN         : String; - numer magazynu
//(Y)WYDRUKOWANY     : Boolean;- czy dokument był drukowany na zwykłej drukarce;
//(Z)ZAFISKALIZOWANY : Boolean; - czy dokument był zafiskalizowany - wydrukowany na drukarce fiskalnej (parametr brany pod uwagę tylko dla dokum. podlegających fiskalizacji);
            )
        );


        $this->pushLineToFile($line);

    }

    /**
     * @param $line
     */
    public function pushLineToFile($line)
    {
        $fileName = $this->getFileName();

        if (file_exists($fileName)) {
            $fp = fopen($fileName, 'a');
        } else {
            $fp = fopen($fileName, 'w');
        }

        foreach ($line as $fields) {
            fputcsv($fp, $fields);
        }

        fclose($fp);
    }


}