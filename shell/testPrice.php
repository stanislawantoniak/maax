<?php
require_once 'abstract.php';

class Modago_Test_Shell extends Mage_Shell_Abstract
{
    public function run()
    {
        $a = 115;
        $b = 140;
        $c = 150;

        $priceBatch = array(
            "10-04B216A-5-010" => array(
                "A" => $a,
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

}

$shell = new Modago_Test_Shell();
$shell->run();

