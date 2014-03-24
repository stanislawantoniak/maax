<?php
/**
 * helper for dhl test
 */
class Zolago_Dhl_Helper_Test {
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
	 * get any pos
	 */
    static public function getPos() {
        return static::getItem('zolagopos/pos');
	}
	
    /**
     * mock shipment
     */
    static public function getMockShipment() {
        $obj = new Zolago_Dhl_Mock_Shipment();
        return $obj;
    }
     
}