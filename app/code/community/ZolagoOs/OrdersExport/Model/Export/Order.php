<?php


class ZolagoOs_OrdersExport_Model_Export_Order
    extends ZolagoOs_OrdersExport_Model_Export_Abstract
{


    public function getFileName()
    {
        $directory = $this->getHelper()->getExportDirectory();
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        return $directory . DS . 'dok.txt';
    }

    public function addOrders($params)
    {
        //krumo($params);

        $line = array(
            array(
                $params['customer_email'],           //DKONTRAH       : String; - identyfikator kontrahenta (numer) (15)
                $params['order_date'],               //DATA            : TDateTime; - Data dokumentu
                'ZA',                                //NAZWADOK        : String; - nazwa dokumentu (10)  opis dozwolonych wartośći w pkt 7.
                $params['order_id'],                 //NRDOK           : String; - numer dokumentu (25)

                '',                                  //TERMIN          : TDateTime; - termin płatności (??????????)
                $params['payment_method'],           //PLATNOSC        : String; - sposób płatności (35)
                $params['order_total'],              //SUMA            : Currency; - Wartość brutto dokumentu - liczone zgodnie z definicją dokumentu
                count($params['order_items']),       //ILEPOZ          : Integer; - ilość pozycji
//    GOTOWKA         : Currency; - zapłata gotówkowa przyjęta na dokumencie
//    DOT_DATA        : TDateTime; - data dokumentu powiązanego (np KP do FA)
//    DOT_NAZWADOK    : String; - nazwa dokumentu powiązanego (10) (np KP do FA)
//    DOT_NRDOK       : String; - numer dokumentu powiązanego (25) (np KP do FA)
//    ANULOWANY       : Boolean; - czy dokument został anulowany
//    UWAGI           : String; - (Memo) Uwagi do dokumentu
//    NRZLEC          : String; - numer zlecenia (20)
//    CECHA_1         : String; - Cecha 1 (35)
//    CECHA_2         : String; - Cecha 2 (35)
//    CECHA_3         : String; - Cecha 3 (35)
//    IDKONTRAHODB    : String; - identyfikator kontrahenta odbierającego dokument (numer) (15)
//    DATAOBOW        : TDateTime; - data obowiązywania zamówienia
//    CECHA_4         : String; - Cecha 4 (35)
//    CECHA_5         : String; - Cecha 5 (35)
//    IDKONTRAHDOST   : String; - identyfikator kontrahenta dostawcy - (numer) (15);
//    MAGAZYN         : String; - numer magazynu
//    WYDRUKOWANY     : Boolean;- czy dokument był drukowany na zwykłej drukarce;
//    ZAFISKALIZOWANY : Boolean; - czy dokument był zafiskalizowany - wydrukowany na drukarce fiskalnej (parametr brany pod uwagę tylko dla dokum. podlegających fiskalizacji);
            )
        );

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