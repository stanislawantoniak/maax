<?php
/**
 * override stocks
 */
class Zolago_CatalogInventory_Model_Resource_Stock
    extends Mage_CatalogInventory_Model_Resource_Stock 
    {
    
    /**
     * Get stock items data for requested products
     *
     * @param Mage_CatalogInventory_Model_Stock $stock
     * @param array $productIds
     * @param bool $lockRows
     * @return array
     */
    public function getProductsStock($stock, $productIds, $lockRows = false)
    {
        if (empty($productIds)) {
            return array();
        }
        $websiteId = Mage::app()->getStore()->getWebsiteId();
        $itemTable = $this->getTable('cataloginventory/stock_item');
        $productTable = $this->getTable('catalog/product');
        $select = $this->_getWriteAdapter()->select()
            ->from(array('si' => $itemTable))
            ->reset(Zend_Db_Select::COLUMNS)
            ->join(array('p' => $productTable), 'p.entity_id=si.product_id', array('type_id'))
            ->join(
                array('pos_stock' => $this->getTable('zolagopos/stock')),
                'pos_stock.product_id = si.product_id',
                array('qty' => 'sum(pos_stock.qty)')
            )
            ->join(
                array('pos_website' => $this->getTable('zolagopos/pos_vendor_website')),
                'pos_website.pos_id = pos_stock.pos_id',
                array()
            )
            ->join(
                array('pos' => $this->getTable('zolagopos/pos')),
                'pos.pos_id = pos_website.pos_id',
                array()
            )
            ->join(
                array('stock_website' => $this->getTable('zolagocataloginventory/stock_website')),
                'stock_website.product_id = si.product_id',
                array('is_in_stock')
            )
            ->where("pos.is_active = ?", Zolago_Pos_Model_Pos::STATUS_ACTIVE)
            ->where('pos_website.website_id = ?',$websiteId) 
            ->where('stock_website.website_id = ?',$websiteId) 
            ->where('stock_id=?', $stock->getId())
            ->where('si.product_id IN(?)', $productIds)
            ->group('si.product_id')
            ->forUpdate($lockRows)
            ->columns(array(
                'si.item_id',
                'si.product_id',
                'si.stock_id',
                'si.min_qty',
                'si.use_config_min_qty',
                'si.is_qty_decimal',
                'si.backorders',
                'si.use_config_backorders',
                'si.min_sale_qty',
                'si.use_config_min_sale_qty',
                'si.max_sale_qty',
                'si.use_config_max_sale_qty',
                'si.low_stock_date',
                'si.notify_stock_qty',
                'si.use_config_notify_stock_qty',
                'si.manage_stock',
                'si.use_config_manage_stock',
                'si.stock_status_changed_auto',
                'si.use_config_qty_increments',
                'si.qty_increments',
                'si.use_config_enable_qty_inc',
                'si.enable_qty_increments',
                'si.is_decimal_divided'
            ));
        return $this->_getWriteAdapter()->fetchAll($select);
    }
   
}