<?php
/**
 * stock from pos
 */
class Zolago_CatalogInventory_Model_Resource_Indexer_Stock_Default 
    extends Mage_CatalogInventory_Model_Resource_Indexer_Stock_Default {
    
    
    protected function _getStockStatusSelect($entityIds = null, $usePrimaryTable = false)
    {
        $adapter = $this->_getWriteAdapter();
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
             
                
                
        $select  = $adapter->select()
            ->from(array('e' => $this->getTable('catalog/product')), array('entity_id'));
        $this->_addWebsiteJoinToSelect($select, true);
        $this->_addProductWebsiteJoinToSelect($select, 'cw.website_id', 'e.entity_id');
        $select->columns('cw.website_id')
            ->join(
                array('cis' => $this->getTable('cataloginventory/stock')),
                '',
                array('stock_id'))
            ->join(
                array('cisi' => $this->getTable('cataloginventory/stock_item')),
                'cisi.stock_id = cis.stock_id AND cisi.product_id = e.entity_id',
                array())
            ->joinLeft(
                array('zcisw' => $this->getTable('zolagocataloginventory/stock_website')),
                'zcisw.website_id = cw.website_id AND zcisw.product_id = e.entity_id',
                array())
            ->columns(array('qty' => 'IFNULL(('.$subselect.'),0)'))
            ->where('cw.website_id != 0')
            ->where('e.type_id = ?', $this->getTypeId());

        // add limitation of status
        $condition = $adapter->quoteInto('=?', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        $this->_addAttributeToSelect($select, 'status', 'e.entity_id', 'cs.store_id', $condition);
        $nonull = $adapter->getCheckSql('zcisw.is_in_stock is NULL',0,1);
        if ($this->_isManageStock()) {
            $statusExpr = $adapter->getCheckSql('cisi.use_config_manage_stock = 0 AND cisi.manage_stock = 0',
                1, $nonull);
        } else {
            $statusExpr = $adapter->getCheckSql('cisi.use_config_manage_stock = 0 AND cisi.manage_stock = 1',
                $nonull, 1);
        }

        $select->columns(array('status' => $statusExpr));

        if (!is_null($entityIds)) {
            $select->where('e.entity_id IN(?)', $entityIds);
        }
        return $select;
    }


}