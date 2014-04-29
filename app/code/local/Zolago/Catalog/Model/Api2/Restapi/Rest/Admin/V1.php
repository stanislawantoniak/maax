<?php
class Zolago_Catalog_Model_Api2_Restapi_Rest_Admin_V1 extends Zolago_Catalog_Model_Api2_Restapi {
    protected function _create() {
        return $this->_retrieve();
    }
    protected function _retrieveCollection() {
        return json_encode(array("testing","hello2"));
    }
    protected function _retrieve() {
        if (json_encode($this->getRequest())) {
            return '{"status":1,"message":"OK"}';
        } else {
            return '{"status":0,"message":"Wrong format"}';        
        }
    }
}