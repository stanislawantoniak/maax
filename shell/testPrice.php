<?php
require_once 'abstract.php';

class Modago_Test_Shell2 extends Mage_Shell_Abstract
{
    public function run()
    {
        $a = 118.99;
        $b = 140;
        $c = 180;

        $priceBatch = array(
            "1-WW1629TL1BXX3/140" => array(
                "A" => $a,
                "B" => $b,
                "salePriceBefore" => $c
            ),
            "10-08B105-4-181" => array(
                "A" => $a,
                "B" => $b,
                "salePriceBefore" => $c
            ),
            "10-08B105-4-182" => array(
                "A" => $a,
                "B" => $b,
                "salePriceBefore" => $c
            ),
            "10-08B105-4-183" => array(
                "A" => $a,
                "B" => $b,
                "salePriceBefore" => $c
            ),
            "10-08B105-4-186" => array(
                "A" => $a,
                "B" => $b,
                "salePriceBefore" => $c
            ),
            "10-08B105-4-184" => array(
                "A" => $a,
                "B" => $b,
                "salePriceBefore" => $c
            ),
            "10-08B105-4-185" => array(
                "A" => $a,
                "B" => $b,
                "salePriceBefore" => $c
            ),



            "10-04B279-5-011" => array(
                "A" => $a+5,
                "B" => $b,
                "salePriceBefore" => $c+5
            ),
            "10-04B279-5-016" => array(
                "A" => $a+5,
                "B" => $b,
                "salePriceBefore" => $c+5
            ),
            "10-04B279-5-014" => array(
                "A" => $a+5,
                "B" => $b,
                "salePriceBefore" => $c+5
            ),
            "10-04B279-5-015" => array(
                "A" => $a+5,
                "B" => $b,
                "salePriceBefore" => $c+5
            ),
            "10-04B279-5-012" => array(
                "A" => $a+5,
                "B" => $b,
                "salePriceBefore" => $c+5
            ),
            "10-04B279-5-013" => array(
                "A" => $a+2,
                "B" => $b,
                "salePriceBefore" => $c+2
            ),






            "25-1BC11829938M" => array(
                "A" => $a+5,
                "B" => $b,
                "salePriceBefore" => $c+5
            ),
            "25-1BC11829936K" => array(
                "A" => $a+5,
                "B" => $b,
                "salePriceBefore" => $c+5
            ),
            "25-1BC11829940F" => array(
                "A" => $a+5,
                "B" => $b,
                "salePriceBefore" => $c+5
            ),
            "25-1BC11829942H" => array(
                "A" => $a+2,
                "B" => $b,
                "salePriceBefore" => $c+2
            ),
            "25-1BC11829944J" => array(
                "A" => $a+5,
                "B" => $b,
                "salePriceBefore" => $c+5
            )
        );

        $vi = new Zolago_Catalog_Model_Api2_Restapi_Rest_Admin_V1();
        $vi::updatePricesConverter($priceBatch);


        Zolago_Catalog_Model_Observer::processConfigurableQueue();
    }
    public function run2() {
        $stock = array (
            '1' => array(
            "1-WW1629TL1BXX3/140" => array (
                'MAGAZYN' => 3,
                'E-SKLEP' => 9
            ),
            "1-WW1629TL1BXX3/134" => array (
                'MAGAZYN' => 4,
                'E-SKLEP' => 9
            ),
            "1-WW1629TL1BXX3/158" => array (
                'MAGAZYN' => 2,
                'E-SKLEP' => 9
            ),
            )
        );
        $vi = new Zolago_Catalog_Model_Api2_Restapi_Rest_Admin_V1();
        $vi::updateStockConverter($stock);
    
    }

}

$shell = new Modago_Test_Shell2();
$shell->run2();
$shell->run();

