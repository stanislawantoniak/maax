<?php
class Zolago_Catalog_Model_Api2_Restapi_Rest_Admin_V1Test extends Zolago_TestCase {

    public function testStock() {
        $obj = Mage::getModel('zolagocatalog/api2_restapi_rest_admin_v1');
        $param =array (
            5 => array (
                '5-9942-L' => array (
                    'SKLEP2' => 0,
                    'SKLEP1' => 3,
                    'MAGAZYN' => 0,
                    'k99' => 0   
                ),
                '5-9942-M' => array (
                    'SKLEP2' => 0,
                    'SKLEP1' => 3,
                    'MAGAZYN' => 0,
                    'k99' => 0   
                ),
                '5-9942-S' => array (
                    'SKLEP2' => 0,
                    'SKLEP1' => 3,
                    'MAGAZYN' => 0,
                    'k99' => 0   
                ),
                '5-9942-XL' => array (
                    'SKLEP2' => 0,
                    'SKLEP1' =>1,
                    'MAGAZYN' => 0,
                    'k99' => 0
                ),
                '5-9942-XXL' => array (
                    'SKLEP2' => 0,
                    'SKLEP1' => 3,
                    'MAGAZYN' => 0,
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
        Mage::log($randomek, null, 'mylog.log');

        $param = array(
        '5-24939-BIALY-XXL' => array(
                'A' => $randomek,
                'B' => 6,
                'C' => 18,
                'Z' => 88
            ),
        '5-24939-BIALY-XL' => array(
                'A' => $randomek+1,
                'B' => 6,
                'C' => 18,
                'Z' => 88
            ),
        '5-24939-BIALY-S' => array(
                'A' => $randomek+2,
                'B' => 6,
                'C' => 18,
                'Z' => 88
            ),
        '5-24939-BIALY-L' => array(
                'A' => $randomek+3,
                'B' => 6,
                'C' => 18,
                'Z' => 88
            ),

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

    public function testCreate()
    {
        $array = array_fill(0, 500000, array('banana', 'lemon', 'peach' => array('banana'), 'banana' => array('banana', 'lemon', 'peach')));

        $obj = Mage::getModel('zolagocatalog/api2_restapi_rest_admin_v1');
        $log = $obj->createTest($array);
        echo $log;

        $this->assertNotEquals($log, FALSE, "File created");
    }

    public function testRead()
    {
        $page = 0;
        $limit = 100;
        $offset = $page * $limit;


        $fileNameParts = array(MAGENTO_ROOT, Zolago_Catalog_Helper_Log::ZOLAGO_API_LOG_FOLDER_TEST, Zolago_Catalog_Helper_Log::ZOLAGO_API_LOG_FILE_NAME);
        $logFile = implode(DS, $fileNameParts) . '.json';


        set_time_limit(60 * 40);
        ini_set('memory_limit', '2024M');
        $content = file_get_contents($logFile);
        $this->assertNotEquals($content, FALSE, "File with empty content");
        $res = json_decode($content);

        $this->assertNotEquals(count($res), 0);
    }

}