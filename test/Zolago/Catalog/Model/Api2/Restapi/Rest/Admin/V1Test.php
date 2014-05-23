<?php
class Zolago_Catalog_Model_Api2_Restapi_Rest_Admin_V1Test extends ZolagoDb_TestCase {

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