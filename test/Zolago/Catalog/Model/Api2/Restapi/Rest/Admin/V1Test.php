<?php
class Zolago_Catalog_Model_Api2_Restapi_Rest_Admin_V1Test extends Zolago_TestCase {

    public function testStock() {
        $obj = Mage::getModel('zolagocatalog/api2_restapi_rest_admin_v1');
        $liczba = 1;
        $param =array (
            10 => array (
                '10-04J462-4-011' => array(
                    'SKLEP2' => 0,
                    'SKLEP1' => 0,
                    'MAGAZYN' => $liczba,
                    'k99' => 0
                ),
                '10-04J462-4-012' => array(
                    'SKLEP2' => 0,
                    'SKLEP1' => 0,
                    'MAGAZYN' => $liczba,
                    'k99' => 0
                ),
                '10-04J462-4-013' => array(
                    'SKLEP2' => 0,
                    'SKLEP1' => 0,
                    'MAGAZYN' => $liczba,
                    'k99' => 0
                ),
                '10-04J462-4-014' => array(
                    'SKLEP2' => 0,
                    'SKLEP1' => 0,
                    'MAGAZYN' => $liczba,
                    'k99' => 0
                ),

                '10-04J462-4-015' => array(
                    'SKLEP2' => 0,
                    'SKLEP1' => 0,
                    'MAGAZYN' => $liczba,
                    'k99' => 0
                ),

                '10-04J462-4-016' => array(
                    'SKLEP2' => 0,
                    'SKLEP1' => 0,
                    'MAGAZYN' => $liczba,
                    'k99' => 0
                ),

            ),
        );
        $obj::updateStockConverter($param);


    }



    public function testPrice() {
        /* @var $obj Zolago_Catalog_Model_Api2_Restapi_Rest_Admin_V1 */
        $obj = Mage::getModel('zolagocatalog/api2_restapi_rest_admin_v1');
//        $param = '{"ProductPricesUpdate":[{"merchant":"4","data":{"32035-20X-L":{"A":10,"B":20,"C":30},
//        "25768-M":{"A":31.9,"B":32.9},
//      "25768-XL":{"A":31.9,"B":32.9},"25767-XXL":{"A":31.9,"B":32.9},"25767-XL":{"A":31.9,"B":32.9},
//      "25768-S":{"A":31.9,"B":32.9},"25767-S":{"A":31.9,"B":32.9}}}]} ';
        $randomek = rand(5, 500);
        Mage::log("nowa cena: ".$randomek, null, 'mylog.log');

        $param = array(
        '5-25678-CZERWONY-XXXXL' => array(
                'A' => $randomek,
                'B' => 6,
                'C' => 18,
                'Z' => 88
            ),
        '5-25680-BIALY-XXXXL' => array(
                'A' => $randomek+1,
                'B' => 6,
                'C' => 18,
                'Z' => 88
            ),
//        '5-24939-BIALY-S' => array(
//                'A' => $randomek+2,
//                'B' => 6,
//                'C' => 18,
//                'Z' => 88
//            ),
//        '5-24939-BIALY-L' => array(
//                'A' => $randomek+3,
//                'B' => 6,
//                'C' => 18,
//                'Z' => 88
//            ),

//        '5123124' => array(
//            'A' => $randomek+3,
//            'B' => 6,
//            'C' => 18,
//            'Z' => 88
//        )
        );
        //$param = json_encode($json);
        $obj::updatePricesConverter($param);
        Zolago_Catalog_Model_Observer::processPriceTypeQueue();
        Zolago_Catalog_Model_Observer::processConfigurableQueue();
    }


}