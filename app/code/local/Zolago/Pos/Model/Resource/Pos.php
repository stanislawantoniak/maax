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
    public function getMinPOSStock()
    {
        $readConnection = $this->_getReadAdapter();

        $select = $readConnection->select();
        $select->from(
            array("main_table" => $this->getTable("zolagopos/pos")),
            array('external_id', 'minimal_stock')
        );
        $select->where("external_id!=?", "");
        $select->where("is_active=?", 1);

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
     * get sku-id associated array
     *
     * @param array $skus
     *
     * @return array
     */
    public static function getSkuAssoc($skus = array())
    {
        $readConnection = Mage::getSingleton('core/resource')
            ->getConnection('core_read');

        $select = $readConnection->select();
        $select
            ->from('catalog_product_entity AS products',
                array(
                     'sku',
                     'product_id' => 'entity_id'
                )
            )
            ->where("products.type_id=?", Mage_Catalog_Model_Product_Type::TYPE_SIMPLE);


        if (!empty($skus)) {
            $select->where("sku IN (?)", $skus);
        }

        $skuAssoc = $readConnection->fetchPairs($select);
        return $skuAssoc;
    }

    /**
     * Calculate stock on open orders
     * @param $merchant
     * @return array
     */
    public function calculateStockOpenOrders($merchant, $skus)
    {
        if (empty($skus)) {
            return array();
        }

        $po_open_order = Mage::getStoreConfig('zolagocatalog/config/po_open_order');
        $adapter = $this->getReadConnection();
        $select = $adapter->select();
        $select
            ->from(
                'udropship_po_item AS po_item',
                array(
                    'sku' => 'po_item.sku',
                    'qty' => 'SUM(po_item.qty)'
                )
            )
            ->join(
                array('products' => 'catalog_product_entity'),
                'po_item.product_id=products.entity_id',
                array()
            )
            ->join(
                array('po' => 'udropship_po'),
                'po.entity_id=po_item.parent_id',
                array()
            )
            ->where("po.udropship_vendor=?", (int)$merchant)
            ->where("po_item.parent_item_id IS NULL")
            ->where("po_item.sku IN(?)", $skus)
            ->where("po.udropship_status IN ({$po_open_order})")
            ->group('po_item.sku');

        $result = $adapter->fetchAssoc($select);

        return $result;
    }

}

