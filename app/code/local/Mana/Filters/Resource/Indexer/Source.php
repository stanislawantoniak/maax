<?php
/**
 * @category    Mana
 * @package     Mana_Filters
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * @author Mana Team
 *
 */
class Mana_Filters_Resource_Indexer_Source extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Indexer_Eav_Source {
	
	
	/**
     * Fix convert string ids to ints (DB load)
     *
     * @param bool $multiSelect
     * @return array
     */
	protected function _getIndexableAttributes($multiSelect) {
		$result = parent::_getIndexableAttributes($multiSelect);
		return array_map(function($item){return (int)$item;}, $result);
	}
	
	 /**
     * Fix convert string ids to ints (DB load)
     *
     * @param array $entityIds      the entity ids limitation
     * @param int $attributeId      the attribute id limitation
     * @return Mage_Catalog_Model_Resource_Product_Indexer_Eav_Source
     */
    protected function _prepareSelectIndex($entityIds = null, $attributeId = null)
    {
		if(is_array($entityIds)){
			$entityIds = array_map(function($item){return (int)$item;}, $entityIds);
		}elseif(is_numeric($entityIds)){
			$entityIds = (int)$entityIds;
		}
		
		if(!is_null($attributeId)){
			$attributeId = (int)$attributeId;
		}
		
		return parent::_prepareSelectIndex($entityIds, $attributeId);
		
    }
	
    public function reindexEntities($processIds) {
//        if (Mage::helper('mana_core')->isMageVersionEqualOrGreater('1.7')) {
//            return parent::reindexEntities($processIds);
//        }
        $adapter = $this->_getWriteAdapter();

        $this->clearTemporaryIndexTable();

        if (!is_array($processIds)) {
            $processIds = array($processIds);
        }

        $parentIds = $this->getRelationsByChild($processIds);
        if ($parentIds) {
            $processIds = array_unique(array_merge($processIds, $parentIds));
        }
        $childIds = $this->getRelationsByParent($processIds);
        if ($childIds) {
            $processIds = array_unique(array_merge($processIds, $childIds));
        }

        $this->_prepareIndex($processIds);
        $this->_prepareRelationIndex($processIds);
        $this->_removeNotVisibleEntityFromIndex();

        $adapter->beginTransaction();
        try {
            // remove old index
            $where = $adapter->quoteInto('entity_id IN(?)', $processIds);
            $adapter->delete($this->getMainTable(), $where);

            // insert new index
            $this->insertFromTable($this->getIdxTable(), $this->getMainTable());

            $adapter->commit();
        } catch (Exception $e) {
            $adapter->rollBack();
            throw $e;
        }

        return $this;
    }
}