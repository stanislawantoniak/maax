<?php

class Zolago_Pos_Model_Resource_Pos extends Mage_Core_Model_Resource_Db_Abstract {

	protected function _construct() {
		$this->_init('zolagopos/pos', "pos_id");
	}
	
	/**
	 * @param Mage_Core_Model_Resource_Db_Collection_Abstract $collection
	 * @return Mage_Core_Model_Resource_Db_Collection_Abstract
	 */
	public function addPosNameToPoCollection(Mage_Core_Model_Resource_Db_Collection_Abstract $collection) {
		$collection->getSelect()->joinLeft(
				array("pos"=>$this->getMainTable()), 
				"main_table.default_pos_id=pos.pos_id", 
				array("default_pos_name"=>"pos.name")
		);
		return $collection;
	}

	/**
	 * @param Varien_Object $pos
	 * @param int $vendorId
	 * @return boolean
	 */
	public function isAssignedToVendor(Varien_Object $pos, $vendorId) {
		$select = $this->getReadConnection()->select();
		$select->from(
				array("pos_vendor"=>$this->getTable("zolagopos/pos_vendor")), 
				array(new Zend_Db_Expr("COUNT(*)"))
		);
		$select->where("pos_vendor.pos_id=?", $pos->getId());
		$select->where("pos_vendor.vendor_id=?", $vendorId);
		return (bool)$this->getReadConnection()->fetchOne($select);
	}


	public function addPosToVendorCollection(Mage_Core_Model_Resource_Db_Collection_Abstract $collection) {
		$collection->getSelect()->joinLeft(
				array("pos_vendor" => $this->getTable('zolagopos/pos_vendor')), "main_table.vendor_id=pos_vendor.vendor_id", array("pos_id")
		)->group("main_table.vendor_id");
	}

	protected function _afterSave(Mage_Core_Model_Abstract $object) {
		if ($object->hasPostVendorIds()) {
			$assignedIds = $object->getPostVendorIds();
			if (is_string($assignedIds)) {
				if ($assignedIds !== "") {
					$assignedIds = Mage::helper('adminhtml/js')->decodeGridSerializedInput($object->getPostVendorIds());
				} else {
					$assignedIds = array();
				}
			}
			$this->_setVendorRelations($assignedIds, $object);
			$object->setPostVendorIds(null);
		}
		parent::_afterSave($object);
	}

	protected function _setVendorRelations($assignedIds, Mage_Core_Model_Abstract $object) {
		$this->_getWriteAdapter()->delete(
				$this->getTable('zolagopos/pos_vendor'), $this->_getWriteAdapter()->quoteInto("pos_id=?", $object->getId())
		);

		$insertData = array();
		foreach ($assignedIds as $id) {
			$insertData[] = array("pos_id" => $object->getId(), "vendor_id" => $id);
		}

		if (count($insertData)) {
			$this->_getWriteAdapter()->insertMultiple($this->getTable('zolagopos/pos_vendor'), $insertData);
		}
	}

	/**
	 * Set times
	 * @param Mage_Core_Model_Abstract $object
	 * @return type
	 */
	protected function _prepareDataForSave(Mage_Core_Model_Abstract $object) {
		if (trim($object->getRegion()) || $object->getRegionId() === "") {
			$object->setRegionId(null);
		} elseif ($object->getRegionId()) {
			$object->setRegion(null);
		}

		if ($object->getVendorOwnerId() === "") {
			$object->setVendorOwnerId(null);
		}

		// Times
		$currentTime = Varien_Date::now();
		if ((!$object->getId() || $object->isObjectNew()) && !$object->getCreatedAt()) {

			$object->setCreatedAt($currentTime);
		}
		$object->setUpdatedAt($currentTime);
		return parent::_prepareDataForSave($object);
	}


    /**
     * @return mixed
     */
    public function getMinPOSStock($vendor)
    {
        $readConnection = $this->_getReadAdapter();

        $select = $readConnection->select();
        $select->from(
            array("main_table" => $this->getTable("zolagopos/pos")),
            array('external_id', 'minimal_stock')
        );
        $select->join(
            array('pos' => $this->getTable("zolagopos/pos_vendor")),
            'pos.pos_id=main_table.pos_id',
            array()
        );
        $select->where("pos.vendor_id=?", (int)$vendor);
        $select->where("main_table.external_id!=?", "");
        $select->where("main_table.is_active=?", 1);

//        SELECT `main_table`.`external_id`, `main_table`.`minimal_stock` FROM `zolago_pos` AS `main_table`
//        INNER JOIN `zolago_pos_vendor` AS `pos` ON pos.pos_id=main_table.pos_id WHERE (pos.vendor_id=5) AND (external_id!='') AND (is_active=1)

        $minPOSValues = $readConnection->fetchPairs($select);
        return $minPOSValues;
    }


    /**
     * get id-sku associated array
     * @return array
     */
    public static function getIdSkuAssoc()
    {
        $readConnection = Mage::getSingleton('core/resource')
            ->getConnection('core_read');

        $select = $readConnection->select();
        $select
            ->from('catalog_product_entity AS products',
                array(
                     'product_id' => 'entity_id',
                     'sku',
                )
            )
            ->where("products.type_id=?", Mage_Catalog_Model_Product_Type::TYPE_SIMPLE);

        $skuAssoc = $readConnection->fetchPairs($select);
        return $skuAssoc;
    }

    /**
     * get products with skuS
     *
     * @param array $skus
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public static function getSkuCollection($skus = array()) {
        /** @var Mage_Catalog_Model_Resource_Product_Collection $collection */
        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection->addFieldToFilter('sku', array("in" => $skus));

        return $collection;
    }

    /**
     * get sku-id associated array
     *
     * @param array $skus
     *
     * @return array
     */
    public static function getSkuAssoc($skus = array())
    {
        $collection = self::getSkuCollection($skus);

        $skuAssoc = array();
        foreach ($collection as $collectionI) {
            $skuAssoc[$collectionI->getSku()] = $collectionI->getId();
        }
        return $skuAssoc;
    }

    /**
     * Calculate stock reserved in POs
     *
     * @param $vendorId
     * @param $skus
     * @return array
     */
    public function calculateStockOpenOrders($vendorId, $skus)
    {
        $res = array();
        if (empty($skus))
            return $res;


        $adapter = $this->getReadConnection();
        $select = $adapter->select();
        $select
            ->from(
                array('po_item' => $this->getTable("udpo/po_item")),
                array(
                    'po_item_id' => 'po_item.entity_id',
                    'sku' => 'po_item.sku',
                    'qty' => new Zend_Db_Expr('SUM(po_item.qty)')
                )
            )
            ->join(
                array('po' => $this->getTable("udpo/po")),
                'po.entity_id=po_item.parent_id',
                array(
                    'po_id' => 'po.entity_id',
                    'po_pos_id' => 'po.default_pos_id',
                    'po_pos_name' => 'po.default_pos_name'
                )
            )
            ->where("po.udropship_vendor=?", (int)$vendorId)
            ->where("po.default_pos_id IS NOT NULL")
            ->where("po_item.parent_item_id IS NULL")
            ->where("po_item.sku IN(?)", $skus)
            ->where("po.reservation=?", 1)
            ->group('po.default_pos_id')
            ->group('po_item.sku');

        $result = $adapter->fetchAll($select);

        if (empty($result))
            return $res;

        //Reformat query result
        foreach ($result as $resultRow) {
            $res[$resultRow['sku']][$resultRow['po_pos_name']] = (int)$resultRow['qty'];
        }

        return $res;
    }

    public function getPosWebsiteRelation($vendorId)
    {
        $adapter = $this->getReadConnection();
        $posVendorWebsiteTable = $this->getTable("zolagopos/pos_vendor_website");

        $select = $adapter->select();

        $select
            ->from(
                array('pos_vendor_website' => $posVendorWebsiteTable),
                array("website_id", "pos_id")
            )
            ->where("vendor_id=?", (int)$vendorId);

        $result = $adapter->fetchAll($select);

        return $result;
    }

}

