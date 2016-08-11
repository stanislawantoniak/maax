<?php

/**
 * mapper queue model
 */
class Zolago_Mapper_Model_Queue_Mapper extends Zolago_Common_Model_Queue_Abstract
{

    public function _construct()
    {
        $this->_init('zolagomapper/queue_mapper');
    }

	/**
	 * @return false|Zolago_Mapper_Model_Queue_Item_Mapper
	 */
    protected function _getItem()
    {
        return Mage::getModel('zolagomapper/queue_item_mapper');
    }

	/**
	 * Return true if no errors with mappers
	 * otherwise false
	 * Mappers with some problems are skipped and message log saved in message for admin
	 *
	 * @return bool
	 */
    protected function _execute()
    {
        $mapperList = array();
        foreach ($this->_collection as $item) {
            $mapperList[$item->getMapperId()] = $item->getMapperId();
        }
		/** @var Zolago_Mapper_Model_Resource_Index $indexer */
        $indexer = Mage::getResourceModel('zolagomapper/index');
        $oldProducts = $indexer->getAssignedProducts($mapperList);
        $productsIds = $indexer->reindexForMappers($mapperList);
        $final = array_merge($oldProducts, $productsIds);
        $final = array_unique($final);
        return $indexer->assignWithCatalog($final);
    }

}
