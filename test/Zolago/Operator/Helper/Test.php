<?php
/**
 * helper for operator tests
 */
class Zolago_Operator_Helper_Test {
    static public function getItem($name) {
        $model = Mage::getModel($name);
        $collection = $model->getCollection();
        $collection->setPageSize(1);
        $collection->load();
        return $collection->getFirstItem();
    }
    /**
     * get any operator
     */
     static public function getOperator() {
        return static::getItem('zolagooperator/operator');
    }
    
    /**
     * get any vendor
     */
    static public function getVendor() {
        return static::getItem('udropship/vendor');
    }
    static public function getFakeVendor() {
        $vendor = Mage::getModel('udropship/vendor');
        $data = self::getVendorData();
        $vendor->setData($data);
        $vendor->setId(1);
        return $vendor;
    }
    static public function getVendorData() {
        $data = array (
            'name' => 'vendorTest',
            'url_key' => 'http://spadajnadrzewo.com',
            'email' => 'pimpekzlasu@spadajnadrzewo.com',
            'street' => 'Bachora Dręczonego 6',
            'billing_street' => 'Pachnąca Moczem 37',
            'city' => 'Bździszewo',
            'billing_city' =>  'Gmochy Wochy',
            'status' => 'A',
        );
        return $data;
    }
    static public function getOperatorData() {
        $data = array (
            'firstname' => 'Stefek',
            'lastname'  => 'Burczymucha',                        
            'email'     => 'pimpekzlasu@vupe.pl',
            'phone'     => '999888777',
            'is_active' => '1',
            'password'  => 'nieznamhasla',
            'roles'     => array('order_operator'),
        );
        return $data;
    }
}
