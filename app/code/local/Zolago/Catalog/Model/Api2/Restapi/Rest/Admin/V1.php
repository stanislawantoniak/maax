<?php
class Zolago_Catalog_Model_Api2_Restapi_Rest_Admin_V1 extends Zolago_Catalog_Model_Api2_Restapi {


    /*test*/
    public function createTest($data)
    {
        $json = json_encode($data);
        $log = Zolago_Catalog_Helper_Log::log($json, TRUE);
        return $log;
    }

    protected function _create($data)
    {
        $json = json_encode($data);
        Zolago_Catalog_Helper_Log::log($json);
        return $json;
    }

    /*--test*/


//    protected function _create() {
//        return json_encode(array("testing","hello"));
//    }
    protected function _retrieveCollection()
    {
        return json_encode(array("testing", "hello2"));
    }

    protected function _retrieve()
    {
        return json_encode($this->getRequest());
        return json_encode(array("testing", "hello3"));
    }
}