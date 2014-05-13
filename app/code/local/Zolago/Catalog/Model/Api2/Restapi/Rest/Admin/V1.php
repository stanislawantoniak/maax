<?php
class Zolago_Catalog_Model_Api2_Restapi_Rest_Admin_V1 extends Zolago_Catalog_Model_Api2_Restapi {
    protected function _create($data) {
        return 'convertproduct?status=OK';
    }
    protected function _retrieveCollection() {
        return json_encode($_GET);
        return json_encode(array("testing","hello2"));
    }
    protected function _retrieve() {
        if (!empty($_GET['status']) &&
            ($_GET['status'] == 'OK')) {
            return json_encode(array('status'=>'OK'));
        } else {
            return json_encode(array('status'=>'Error'));        
        }
    }
}