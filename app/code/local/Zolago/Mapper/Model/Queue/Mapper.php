<?php
/**
 * mapper queue model
 */
class Zolago_Mapper_Model_Queue_Mapper extends Zolago_Common_Model_Queue_Abstract {

    public function _construct() { 
        $this->_init('zolagomapper/queue_mapper');        
    }

    protected function _getItem() {
        return Mage::getModel('zolagomapper/queue_item_mapper');
    }

    
    protected function _execute() {
        $mapperList = array();
        foreach ($this->_collection as $item) {
            $mapperList[$item->getMapperId()] = $item->getMapperId();
        }
        $indexer = Mage::getResourceModel('zolagomapper/index');
        $oldProducts = $indexer->getAssignedProducts($mapperList);
        $productsIds = $indexer->reindexForMappers($mapperList);        
        $final = array_merge($oldProducts,$productsIds);
        $final = array_unique($final);
        $indexer->assignWithCatalog($final);
    }

    /**
     * Push to mapper queue
     * @param $setIds
     */
    public function pushAttributeSetToMapperQueue($setIds)
    {
        $collection = Mage::getModel("zolagomapper/mapper")->getCollection();
        $collection->addFieldToFilter("attribute_set_id", array("in" => $setIds));
        $mappers = $collection->getAllIds();

        if ($collection->getAllIds()) {
            foreach ($mappers as $mapper) {
                Mage::getModel("zolagomapper/queue_mapper")->push($mapper);
            }
        }
    }
}
