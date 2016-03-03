<?php
/**
 * @method Zolago_Dropship_Model_Session _getSession()
 */
class Zolago_Catalog_Controller_Vendor_Price_Abstract
    extends Zolago_Catalog_Controller_Vendor_Abstract
{

    /**
     * @return array
     */
    protected function _getAvailableSortParams() {
        return $this->_getCollection()->getAvailableSortParams();
    }

    /**
     * @return array
     */
    protected function _getAvailableQueryParams() {
        return $this->_getCollection()->getAvailableQueryParams();
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    protected function _getSqlCondition($key, $value) {
        if(is_array($value)) {

            if(isset($value['to']) && is_numeric($value['to'])) {
                $value['to'] = (float)$value['to'];
            }
            if(isset($value['from']) && is_numeric($value['from'])) {
                $value['from'] = (float)$value['from'];
            }

            if(isset($value['to']) && is_numeric($value['to']) &&
                    (!isset($value['from']) || (isset($value['from']) && $value['from']==0))) {
                $value = array($value, array("null"=>true));
            }

            return $value;
        }
        switch ($key) {
            case "is_new":
            case "is_bestseller":
                return $value == 1 ? array("eq" => $value) : array(array("null" => true), array("eq" => $value));
                break;
            case "product_flags":
            case "is_in_stock":
                return array("eq" => $value);
                break;
            case "campaign_regular_id":
                return $value != 0 ? array("eq" => $value) : array(
                    array(
                        array("null" => true),
                        array("eq" => 0)
                    )
                );
                break;
            case "converter_price_type":
            case "converter_msrp_type":
                return $value != 0 ? array("eq" => $value) : array("null" => true);
                break;
            case "msrp":
                return $value == 1 ? array("notnull" => true) : array(array("null" => true));
                break;
        }
        return array("like"=>'%'.$value.'%');
    }



    /**
     * collection dont use after load - just flat selects
     * @param Mage_Catalog_Model_Resource_Product_Collection
     * @return Zolago_Catalog_Model_Resource_Vendor_Price_Collection
     */
    protected function _prepareCollection(Varien_Data_Collection $collection=null) {
        $visibilityModel = Mage::getSingleton("catalog/product_visibility");
        /* @var $visibilityModel Mage_Catalog_Model_Product_Visibility */

        if(!($collection instanceof Mage_Catalog_Model_Resource_Product_Collection)) {
            $collection = Mage::getResourceModel("zolagocatalog/vendor_price_collection");
        }
        /* @var $collection Zolago_Catalog_Model_Resource_Vendor_Price_Collection */
        $storeId = Mage::app()->getRequest()->getParam("store_id");
        $collection->setStoreId($storeId);
        $collection->addAttributes();
        $collection->joinAdditionalData();

        //$websiteId = Mage::getModel('core/store')->load($storeId)->getWebsiteId();
        //$collection->addStoreFilter($store->getId());
        //$collection->addWebsiteFilter($websiteId);

        // Filter visible
        $collection->addAttributeToFilter("visibility",
                                          array("neq"=>$visibilityModel::VISIBILITY_NOT_VISIBLE), "inner");

        // Filter dropship
        $collection->addAttributeToFilter("udropship_vendor", $this->getVendor()->getId(), "inner");



        return $collection;
    }

    /**
     * set attributes from price grid
     *
     * @param array $productIds
     * @param array $attributes
     * @param int $storeId
     * @param array $data
     * @return $this|Zolago_Catalog_Controller_Vendor_Abstract
     * @throws Exception
     * @throws Mage_Core_Exception
     */
    protected function _processAttributresSave(array $productIds, array $attributes, $storeId, array $data) {

        $collection = Mage::getResourceModel("zolagocatalog/vendor_price_collection");
        $inventoryData = array();

        // Vaild collection
        $collection->addAttributeToFilter('udropship_vendor', $this->getVendor()->getId());
        $collection->addIdFilter($productIds);

        if($collection->getSize()<count($productIds)) {
            throw new Mage_Core_Exception("You are trying to edit not your product");
        }

        /* @var $collection Zolago_Catalog_Model_Resource_Vendor_Price_Collection */

        foreach($attributes as $attributeCode=>$value) {
            if(!in_array($attributeCode, $collection->getEditableAttributes())) {
                throw new Mage_Core_Exception("You are trying to edit not editable attribute (".htmlspecialchars($attributeCode).")");
            }

            // Process modified flow attributes
            switch($attributeCode) {
            case "politics":
                $inventoryData['politics'] = $value;
                unset($attributes[$attributeCode]);
                break;
            case "status":
                // change status in child products
                $list = Mage::getResourceModel('catalog/product')->getRelatedProducts($productIds);
                $childProds = array();
                foreach ($list as $item) {
                    $childProds[$item['product_id']] = $item['product_id'];
                }
        		if ($childProds) {
		            $childAttributes = array(
		                'status' => $attributes['status']
		                );            
                    Mage::getSingleton('catalog/product_action')
		                ->updateAttributes($childProds, $childAttributes, $storeId);
                }	
                break;                                                                                    
            }
        }

        if($attributes) {
            /* @var $actionModel Mage_Catalog_Model_Product_Action */
            $actionModel = Mage::getSingleton('catalog/product_action');
            $actionModel->updateAttributes($productIds, $attributes, $storeId);
        }

        // Prepare stock
        foreach (Mage::helper('cataloginventory')->getConfigItemOptions() as $option) {
            if (isset($inventoryData[$option]) && !isset($inventoryData['use_config_' . $option])) {
                $inventoryData['use_config_' . $option] = 0;
            }
        }

        // Stock save
        if ($inventoryData) {
            /** @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
            $stockItem = Mage::getModel('cataloginventory/stock_item');
            $stockItem->setProcessIndexEvents(false);
            $stockItemSaved = false;

            // Preparing collection of stock items
            /** @var Mage_CatalogInventory_Model_Resource_Stock_Item_Collection $collStockItems */
            $collStockItems = Mage::getModel('cataloginventory/stock')->getItemCollection();
            $collStockItems->addFieldToFilter('product_id', array('in' => $productIds));

            foreach ($productIds as $productId) {

                $stockItem = $collStockItems->getItemByColumnValue('product_id', $productId);
                $stockDataChanged = false;
                foreach ($inventoryData as $k => $v) {
                    if ($k == 'politics') {
                        $type = $stockItem->getTypeId();
                        if ($type == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
                            // Simple
                            $stockItem->setData('use_config_min_qty',1-$v);
                            $stockItem->setData('min_qty',1000000*$v);
                        } else {
                            // Configurable
                            $stockItem->setData('use_config_manage_stock', 0);
                            $stockItem->setData('manage_stock',$v);
                            $stockItem->setData('is_in_stock',
                                !$v ? Mage_CatalogInventory_Model_Stock::STOCK_IN_STOCK :
                                     Mage_CatalogInventory_Model_Stock::STOCK_OUT_OF_STOCK);
                        }

                        $stockDataChanged = true;
                    } else {
                        $stockItem->setDataUsingMethod($k, $v);
                        if ($stockItem->dataHasChangedFor($k)) {
                            $stockDataChanged = true;
                        }
                    }
                }
                if ($stockDataChanged) {
                    $stockItem->save();
                    $stockItemSaved = true;
                }
            }

            if ($stockItemSaved) {
                Mage::getSingleton('index/indexer')->indexEvents(
                    Mage_CatalogInventory_Model_Stock_Item::ENTITY,
                    Mage_Index_Model_Event::TYPE_SAVE
                );
            }
        }
        return $this;
    }
}



