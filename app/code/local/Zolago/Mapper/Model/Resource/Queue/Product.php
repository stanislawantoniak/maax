<?php
/**
 * resource model for product queue
 */
class Zolago_Mapper_Model_Resource_Queue_Product extends Zolago_Common_Model_Resource_Queue_Abstract {
    protected function _construct() {
        $this->_init('zolagomapper/queue_product','queue_id');
    }

    /**
     * Push products to mapper queue
     * @param $productIds
     */
    public function pushProductToMapperQueue($productIds)
    {
        $insert = array();
        foreach ($productIds as $productId) {
            $insert[] = array("product_id" => $productId);
        }
        try{
            $this->_getWriteAdapter()->insertMultiple(
                $this->getMainTable(),
                $insert
            );
        } catch(Exception $e){
            Mage::logException($e);
        }
    }
}

