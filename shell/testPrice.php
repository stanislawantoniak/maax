<?php
require_once 'abstract.php';

class Modago_Test_Shell2 extends Mage_Shell_Abstract
{
    public function run()
    {
        $a = 100;
        $b = 140;
        $c = 141;

        $priceBatch = array(
            "1-WTSLEDDKLR4/098" => array(
                "A" => $a,
                "B" => $b,
                "salePriceBefore" => $c
            ),
            "1-WTSLEDDKLR4/092" => array(
                "A" => $a,
                "B" => $b,
                "salePriceBefore" => $c
            ),
            "1-WTSLEDDKLR4/086" => array(
                "A" => $a,
                "B" => $b,
                "salePriceBefore" => $c
            ),
            "1-WTSLEDDKLR4/080" => array(
                "A" => $a,
                "B" => $b,
                "salePriceBefore" => $c
            ),
            "1-WTSLEDDKLR4/074" => array(
                "A" => $a,
                "B" => $b,
                "salePriceBefore" => $c
            ),
            "10-08B105-4-184" => array(
                "A" => $a+810,
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
            "1-WTSLEDDKLR4/098" => array (
                'MAGAZYN' => 0,
                'E-SKLEP' => 0
            ),
            "1-WTSLEDDKLR4/092" => array (
                'MAGAZYN' => 0,
                'E-SKLEP' => 0
            ),
            "1-WTSLEDDKLR4/086" => array (
                'MAGAZYN' => 2,
                'E-SKLEP' => 9
            ),
            "1-WTSLEDDKLR4/080" => array (
                'MAGAZYN' => 2,
                'E-SKLEP' => 9
            ),
            "1-WTSLEDDKLR4/074" => array (
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

