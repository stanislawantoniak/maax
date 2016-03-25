<?php
/**
 * is_in_stock by website
 */
class Zolago_CatalogInventory_Model_Resource_Stock_Website
    extends Mage_Core_Model_Resource_Db_Abstract {

    protected function _construct() {
        $this->_init('zolagocataloginventory/stock_website','unique_id');
    }

    /**
     * save is_in_stock for one product/website
     */

    public function saveStockWebsite(Mage_CatalogInventory_Model_Stock_Item $object)
    {
        $qty = $object->getWebsiteQty();
        $instock = $object->getInStock();
        $productId = $object->getProductId();
        $adapter = $this->_getWriteAdapter();
        foreach ($instock as $website=>$status) {
            $bind = array(
                        'product_id' => $productId,
                        'website_id' => $website,
                        'is_in_stock'=> !empty($qty[$website])? $status:0,  // save only if qty > 0
                    );
            $adapter->insertOnDuplicate($this->getMainTable(),$bind,array('is_in_stock'));
        }
        return $this;
    }

    /**
     * save is_in_stock for group
     * @param array $isInStock
     */

    public function saveCatalogInventoryStock($isInStock) {
        $insert = array();
        foreach ($isInStock as $productId => $website) {
            foreach ($website as $websiteId => $value) {
                $insert[] = sprintf('(%d,%d,%d)',$productId,$websiteId,$value);
            }
        }
        if (count($insert)) {
            $insertLines = implode(',',$insert);
            $this->beginTransaction();
            try {
                $insert = sprintf(
                              "INSERT INTO %s (product_id,website_id,is_in_stock) VALUES %s "
                              . " ON DUPLICATE KEY UPDATE is_in_stock=VALUES(is_in_stock)",
                              $this->getMainTable(), $insertLines
                          );
                $this->_getWriteAdapter()->query($insert);
                $this->_getWriteAdapter()->commit();
            } catch (Exception $e) {
                $this->rollBack();
                Mage::logException($e);
            }


        }
        return $this;
    }

}