<?php
/**
 * helper for pos tests
 */
class Zolago_Pos_Helper_Test {
    static public function getItem($name) {
        $model = Mage::getModel($name);
        $collection = $model->getCollection();
        $collection->setPageSize(1);
        $collection->load();
        return $collection->getFirstItem();
    }
    /**
     * get any pos
     */
     static public function getPos() {
        return static::getItem('zolagopos/pos');
    }
    
    /**
     * get any vendor
     */
    static public function getVendor() {
        return static::getItem('udropship/vendor');
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
        );
        return $data;
    }
    static public function getPosData() {
        $data = array (
            'name' => 'posTest',
            'minimal_stock' => 1,
            'email' => 'pimpekzlasu@spadajnadrzewo.com',
            'street' => 'Bachora Dręczonego 6',
            'country_id' => 'PL',
            'region_id' => 1,
            'city' => 'Bździszewo',
            'postcode' => '00-009',
            'phone' => '999888777',
            'priority' => 0,
        );
        return $data;
    }
}
