<?php
require_once 'abstract.php';

class Modago_Test_Shell2 extends Mage_Shell_Abstract
{
    public function run()
    {
        $a = 118.90;
        $b = 140;
        $c = 180;

        $priceBatch = array(
            "10-04B216A-5-010" => array(
                "A" => $a-1,
                "B" => $b,
                "salePriceBefore" => $c
            ),
            "10-04B216A-5-011" => array(
                "A" => $a,
                "B" => $b,
                "salePriceBefore" => $c
            ),
            "10-04B216A-5-012" => array(
                "A" => $a,
                "B" => $b,
                "salePriceBefore" => $c
            ),
            "10-04B216A-5-013" => array(
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






            "8-LAK1PC" => array(
                "A" => $a+5,
                "B" => $b,
                "salePriceBefore" => $c+5
            ),
            "8-LAK1PB" => array(
                "A" => $a+5,
                "B" => $b,
                "salePriceBefore" => $c+5
            ),
            "28-LAK1PE" => array(
                "A" => $a+5,
                "B" => $b,
                "salePriceBefore" => $c+5
            ),
            "8-LAK1PD" => array(
                "A" => $a+2,
                "B" => $b,
                "salePriceBefore" => $c+2
            ),
            "8-LAK1PF" => array(
                "A" => $a+5,
                "B" => $b,
                "salePriceBefore" => $c+5
            )
        );

        $vi = new Zolago_Catalog_Model_Api2_Restapi_Rest_Admin_V1();
        $vi::updatePricesConverter($priceBatch);


        Zolago_Catalog_Model_Observer::processConfigurableQueue();
    }

}

$shell = new Modago_Test_Shell2();
$shell->run();

