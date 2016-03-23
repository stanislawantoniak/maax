<?php
/**
 * indexer configurable
 */
class Zolago_CatalogInventory_Model_Resource_Indexer_Stock_Configurable
    extends Mage_CatalogInventory_Model_Resource_Indexer_Stock_Configurable {

    /**
     * Get the select object for get stock status by product ids
     *
     * @param int|array $entityIds
     * @param bool $usePrimaryTable use primary or temporary index table
     * @return Varien_Db_Select
     */
    protected function _getStockStatusSelect($entityIds = null, $usePrimaryTable = false)
    {
        $adapter  = $this->_getWriteAdapter();
        $idxTable = $usePrimaryTable ? $this->getMainTable() : $this->getIdxTable();
        $select  = $adapter->select()
                   ->from(array('e' => $this->getTable('catalog/product')), array('entity_id'));
        $this->_addWebsiteJoinToSelect($select, true);
        $this->_addProductWebsiteJoinToSelect($select, 'cw.website_id', 'e.entity_id');
        
        $subselect = $adapter->select()
            ->from(array('stock' => $this->getTable('zolagopos/stock')))
            ->join(array('website' => $this->getTable('zolagopos/pos_vendor_website')),
                'website.pos_id = stock.pos_id',
                array())
            ->join(array('pos' => $this->getTable('zolagopos/pos')),
                'pos.pos_id = stock.pos_id',
                array())
             ->where('pos.is_active = ?', Zolago_Pos_Model_Pos::STATUS_ACTIVE)
             ->where('website.website_id = cw.website_id')
             ->where('stock.product_id = e.entity_id')
             ->group('stock.product_id')
             ->reset(Zend_Db_Select::COLUMNS)
             ->columns(array('qty' => 'sum(stock.qty)'));
        
                
        $select->columns('cw.website_id')
        ->join(
            array('cis' => $this->getTable('cataloginventory/stock')),
            '',
            array('stock_id'))
        ->joinLeft(
            array('cisi' => $this->getTable('cataloginventory/stock_item')),
            'cisi.stock_id = cis.stock_id AND cisi.product_id = e.entity_id',
            array())
        ->joinLeft(
            array('l' => $this->getTable('catalog/product_super_link')),
            'l.parent_id = e.entity_id',
            array())
        ->join(
            array('le' => $this->getTable('catalog/product')),
            'le.entity_id = l.product_id',
            array())
        ->joinLeft(
            array('i' => $idxTable),
            'i.product_id = l.product_id AND cw.website_id = i.website_id AND cis.stock_id = i.stock_id',
            array())
        ->joinLeft(
            array('zcisw' => $this->getTable('zolagocataloginventory/stock_website')),
            'zcisw.website_id = cw.website_id AND zcisw.product_id = e.entity_id',
            array())

//        ->columns(array('qty' => new Zend_Db_Expr('0')))
        ->columns(array('qty' => 'IFNULL(('.$subselect.'),0)'))
            
        ->where('cw.website_id != 0')
        ->where('e.type_id = ?', $this->getTypeId())
        ->group(array('e.entity_id', 'cw.website_id', 'cis.stock_id'));

        $psExpr = $this->_addAttributeToSelect($select, 'status', 'e.entity_id', 'cs.store_id');
        $psCond = $adapter->quoteInto($psExpr . '=?', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);

        if ($this->_isManageStock()) {
            $statusExpr = $adapter->getCheckSql('cisi.use_config_manage_stock = 0 AND cisi.manage_stock = 0',
                                                1, 'zcisw.is_in_stock');
        } else {
            $statusExpr = $adapter->getCheckSql('cisi.use_config_manage_stock = 0 AND cisi.manage_stock = 1',
                                                'zcisw.is_in_stock', 1);
        }

        $optExpr = $adapter->getCheckSql("{$psCond} AND le.required_options = 0", 'i.stock_status', 0);
        $stockStatusExpr = $adapter->getLeastSql(array("MAX({$optExpr})", "MIN({$statusExpr})"));

        $select->columns(array(
                             'status' => $stockStatusExpr
                         ));

        if (!is_null($entityIds)) {
            $select->where('e.entity_id IN(?)', $entityIds);
        }
        return $select;
    }

}