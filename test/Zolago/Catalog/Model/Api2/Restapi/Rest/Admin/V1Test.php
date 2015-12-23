<?php
class Zolago_Catalog_Model_Api2_Restapi_Rest_Admin_V1Test extends Zolago_TestCase {

    public function testStock() {
        $obj = Mage::getModel('zolagocatalog/api2_restapi_rest_admin_v1');
        $liczba = rand(5, 500);
        $param =array (
            5 => array (
                '5-8325-85C' => array(
                    'SKLEP2' => 0,
                    'SKLEP1' => 0,
                    'MAGAZYN' => $liczba,
                    'k99' => 0
                ),
                '5-8325-85B' => array(
                    'SKLEP2' => 0,
                    'SKLEP1' => 0,
                    'MAGAZYN' => $liczba,
                    'k99' => 0
                ),
                '5-8325-75E' => array(
                    'SKLEP2' => 0,
                    'SKLEP1' => 0,
                    'MAGAZYN' => $liczba,
                    'k99' => 0
                ),
                '5-8325-75D' => array(
                    'SKLEP2' => 0,
                    'SKLEP1' => 0,
                    'MAGAZYN' => $liczba,
                    'k99' => 0
                ),
                '5-8325-70E' => array(
                    'SKLEP2' => 0,
                    'SKLEP1' => 0,
                    'MAGAZYN' => $liczba,
                    'k99' => 0
                ),
                '5-8325-70C' => array(
                    'SKLEP2' => 0,
                    'SKLEP1' => 0,
                    'MAGAZYN' => $liczba,
                    'k99' => 0
                ),
                '5-8325-70B' => array(
                    'SKLEP2' => 0,
                    'SKLEP1' => 0,
                    'MAGAZYN' => $liczba,
                    'k99' => 0
                ),
                '5-8325-65C' => array(
                    'SKLEP2' => 0,
                    'SKLEP1' => 0,
                    'MAGAZYN' => $liczba,
                    'k99' => 0
                ),
//                '5-30025-BEZOWY-38' => array (
//                    'SKLEP2' => 0,
//                    'SKLEP1' => $liczba,
//                    'MAGAZYN' => 0,
//                    'k99' => 0
//                ),
//                '5-30025-BEZOWY-40' => array (
//                    'SKLEP2' => 0,
//                    'SKLEP1' =>$liczba,
//                    'MAGAZYN' => 0,
//                    'k99' => 0
//                ),
//                '5-30025-BEZOWY-42' => array (
//                    'SKLEP2' => 0,
//                    'SKLEP1' => $liczba,
//                    'MAGAZYN' => 0,
//                    'k99' => 0
//                ),
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
        '25-1AU41410440G' => array(
                'A' => $randomek,
                'B' => 6,
                'C' => 18,
                'Z' => 88
            ),
        '25-16X83123136O' => array(
                'A' => $randomek+1,
                'B' => 6,
                'C' => 18,
                'Z' => 88
            ),
        '25-19E40694342D' => array(
                'A' => $randomek+2,
                'B' => 6,
                'C' => 18,
                'Z' => 88
            ),
        '25-19E40694344F' => array(
                'A' => $randomek+3,
                'B' => 6,
                'C' => 18,
                'Z' => 88
            ),

        '25-19E40694340B' => array(
            'A' => $randomek+3,
            'B' => 6,
            'C' => 18,
            'Z' => 88
        ),
            '25-19E40694338I' => array(
                'A' => $randomek+3,
                'B' => 6,
                'C' => 18,
                'Z' => 88
            ),
            '25-19E40694338I' => array(
                'A' => $randomek+3,
                'B' => 6,
                'C' => 18,
                'Z' => 88
            )
        );
        //$param = json_encode($json);
        $obj::updatePricesConverter($param);
        Zolago_Catalog_Model_Observer::processPriceTypeQueue();
        Zolago_Catalog_Model_Observer::processConfigurableQueue();
    }


}