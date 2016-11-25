<?php
class Zolago_Solrsearch_Model_Observer {

    /** @var Mage_Catalog_Model_Product $_tmpProduct */
    protected $_tmpProduct;

    /** Products to index
     * @var array $_collectedProducts */
    protected $_collectedProducts = array();

    /** Should queue be handled
     * @var bool $_canBeHandled */
    protected $_canBeHandled = true;

    /** holds module helper
     * @var Zolago_Solrsearch_Helper_Data */
    protected $_helper;

    public function cronProcessQueue() {
        $this->getQueue()->process();
    }

    public function cronCleanupQueue() {
        $this->getQueue()->cleanup();
    }

    /**
     * @return Zolago_Solrsearch_Model_Queue
     */
    public function getQueue() {
        return Mage::getSingleton('zolagosolrsearch/queue');
    }

    /**
     * @return Zolago_Solrsearch_Model_Queue_Item
     */
    public function getQueueItem() {
        return Mage::getModel('zolagosolrsearch/queue_item');
    }

    /**
     * Process converter stock save
     * @param Varien_Event_Observer $observer
     */
    public function zolagoCatalogConverterStockSaveBefore(Varien_Event_Observer $observer) {
        $this->collectProducts($observer->getEvent()->getProductId(),true);
    }

    /**
     * After all stock changed - process collected products
     * @param Varien_Event_Observer $observer
     */
    public function zolagoCatalogConverterStockComplete(Varien_Event_Observer $observer) {
        $this->processCollectedProducts();
    }

    /**
     * Add product to queue.
     * @param Varien_Event_Observer $observer
     */
    public function catalogProductDeleteBefore(Varien_Event_Observer $observer) {
        $product = $observer->getEvent()->getProduct();
        if(!($product instanceof Mage_Catalog_Model_Product)) {
            return;
        }
        $this->collectProducts($product->getId(), true);
        $this->processCollectedProducts();
    }


    /**
     * Add product to queue.
     * @param Varien_Event_Observer $observer
     */
    public function catalogProductSaveAfter(Varien_Event_Observer $observer) {
        $product = $observer->getEvent()->getProduct();
        if(!($product instanceof Mage_Catalog_Model_Product)) {
            return;
        }

        /**
         * @todo add check solr-used attribute changed?
         */

        $this->collectProducts($product->getId(), true);
    }

    /**
     * Before category Delete
     * @param Varien_Event_Observer $observer
     */
    public function catalogCategoryDeleteBefore(Varien_Event_Observer $observer) {
        $category = $observer->getEvent()->getCategory();
        /* @var $category Mage_Catalog_Model_Category */

        $regularIds = $category
                      ->getProductCollection()
                      ->getAllIds();

        $this->collectProducts($regularIds);

        $this->_canBeHandled = false;
    }


    /**
     * Category delete after commit
     * @param Varien_Event_Observer $observer
     */
    public function catalogCategoryDeleteAfter(Varien_Event_Observer $observer) {
        $this->_canBeHandled = true;
    }


    /**
     * Collect produc of category save
     * @param Varien_Event_Observer $observer
     */
    public function catalogCategorySaveAfter(Varien_Event_Observer $observer) {
        $category = $observer->getEvent()->getCategory();
        /* @var $category Mage_Catalog_Model_Category */

        ////////////////////////////////////////////////////////////////////////
        // Did product changed on depends attributes chnaged?
        ////////////////////////////////////////////////////////////////////////
        $shouldProcess = false;

        $affectedIds = $category->getAffectedProductIds();
        if(!$affectedIds) {
            $affectedIds = array();
        } else {
            $shouldProcess = true;
        }

        foreach($this->_getChangableCategoryAttributes() as $attrCode) {
            if($category->getData($attrCode)!=$category->getOrigData($attrCode)) {
                $shouldProcess = true;
                break;
            }
        }

        if(!$shouldProcess) {
            return;
        }

        $regularIds = $category->getProductCollection()->getAllIds();

        $productsIds = array_unique($affectedIds + $regularIds);

        $this->collectProducts($productsIds);
    }


    /**
     * @param Varien_Event_Observer $observer
     */
    public function catalogProductAttributeUpdateAfter(
        Varien_Event_Observer $observer) {

        $event = $observer->getEvent();
        $productIds = $event->getProductIds();
        $storeId = $event->getStoreId();

        /**
         * @todo add check solr-used attribute changed?
         */

        $this->collectProducts($productIds, true);

        $this->processCollectedProducts();
    }


    /**
     * After mapper assign products
     * @param Varien_Event_Observer $observer
     */
    public function zolagoMapperAfterAssignProducts(
        Varien_Event_Observer $observer) {


        $event = $observer->getEvent();
        $productIds = $event->getProductIds();

        $this->collectProducts($productIds, true);
        $this->processCollectedProducts();

    }

    public function zolagoCatalogAfterUpdateProducts(Varien_Event_Observer $observer)
    {
        $event = $observer->getEvent();
        $productIds = $event->getProductIds();

        $this->collectProducts($productIds);
    }


    /**
     * @param Varien_Event_Observer $observer
     */
    public function afterReindexProcessCatalogProductPrice(
        Varien_Event_Observer $observer) {

    }

    /**
     * Process after response send - if has some collected products process it
     * @param Varien_Event_Observer $observer
     */
    public function controllerFrontSendResponseAfter(
        Varien_Event_Observer $observer=null) {

        if(!empty($this->_collectedProducts)) {
            $this->processCollectedProducts();
        }
    }

    public function catalogInventorySave(Varien_Event_Observer $observer)
    {
        $event = $observer->getEvent();
        $_item = $event->getItem();
        $productId = $_item->getData("product_id");

        $isInStock = (int)$_item->getData("is_in_stock");
        $type_id = $_item->getData("type_id");

        if ($type_id == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE && $isInStock == 0) {
            $_product = Mage::getModel("catalog/product")->load($productId);
            $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($_product->getId());

            if (!empty($parentIds) && isset($parentIds[0])) {
                $this->collectProducts($productId);
                $this->processCollectedProducts();
            }
        }
    }
    /**
     * Process prices after catalog update via converter
     * @param Varien_Event_Observer $observer
     */
    public function catalogConverterPriceUpdateAfter(Varien_Event_Observer $observer) {
        $this->collectProductsAndPushToQueue($observer);
    }

    /**
     * Push to solr
     * Collect product (as ids) and push to solr queue
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function collectProductsAndPushToQueue(Varien_Event_Observer $observer) {
        $event        = $observer->getEvent();
        $productIds   = $event->getProductIds();
        $checkParents = $event->hasData('check_parents') ? $event->getData('check_parents') : false;


        $this->collectProducts($productIds,$checkParents);
        $this->processCollectedProducts();
        return $this;
    }

    /**
     * @param array|int|Mage_Catalog_Model_Product $productIds
     * @param bool $checkParents
     * @return void
     */
    public function collectProducts($productIds,$checkParents=false) {
        //normalize input data
        if(is_numeric($productIds)) {
            $productIds = array($productIds);
        }
        elseif($productIds instanceof Mage_Catalog_Model_Product) {
            $productIds = array($productIds->getId());
        }
        elseif(!is_array($productIds)) {
            return;
        }

        if($checkParents) {
            /* @var $resource Zolago_Solrsearch_Model_Resource_Improve */
            $resource = Mage::getResourceModel("zolagosolrsearch/improve");
            $productIds = array_merge($productIds,$resource->getParentIdsByChild($productIds, true));
        }

        if(!empty($productIds) && is_array($productIds)) {
            /** @var Mage_Core_Model_Resource $resource */
            $resource = Mage::getSingleton('core/resource');
            $readConnection = $resource->getConnection('core_read');
            $tableName = $resource->getTableName('catalog/product');
            $websiteTable = $resource->getTableName('catalog/product_website');
            $storeTable = $resource->getTableName('core/store');
            $statusAttributeId = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', 'status');
            $visibilityAttributeId = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', 'visibility');

            $query = "SELECT
                     `product`.`entity_id` AS `id`,
                     IFNULL(`s`.`value`,
                     (SELECT `ds`.`value` FROM `".$tableName."_int` AS `ds`
                     WHERE `ds`.`entity_id` = `product`.`entity_id` AND
                     `ds`.`attribute_id` = '".$statusAttributeId."' AND `ds`.`store_id` = 0)
                     ) AS `status`,
                     `v`.`value` AS `visibility`,
                     `store`.`store_id` AS `store_id`
                     FROM `".$tableName."` AS `product`

                     LEFT JOIN `".$websiteTable."` AS `website` ON
                     `website`.`product_id` = `product`.`entity_id`

                     LEFT JOIN `".$storeTable."` AS `store` ON
                     `store`.`website_id` = `website`.`website_id`

                     LEFT JOIN `".$tableName."_int` AS `s` ON
                     `s`.`entity_id` = `product`.`entity_id` AND
                     `s`.`attribute_id` = '".$statusAttributeId."' AND
                     `s`.`store_id` = `store`.`store_id`

                     LEFT JOIN `".$tableName."_int` AS `v` ON

                     `v`.`entity_id` = `product`.`entity_id` AND
                     `v`.`attribute_id` = '".$visibilityAttributeId."'

                     WHERE `product`.`entity_id` IN(".implode(",",$productIds) .") AND `store`.`store_id` IS NOT NULL";

            $this->_collectedProducts = array_merge($this->_collectedProducts,$readConnection->fetchAll($query));
        }
    }


    /**
     * Process collected products
     */
    public function processCollectedProducts() {
        if(!$this->_canBeHandled) {
            return;
        }

        $this->_pushMultipleProducts($this->_collectedProducts);
        $this->_collectedProducts = array();
    }

    /**
     * Multiple products push do queue
     *
     * @param array $products
     */
    protected function _pushMultipleProducts($products=array()) {
        $queueItems = array();

        $rawItem = $this->getQueueItem();

        foreach ($products as $product) {

            // Check should be removed
            if ($product['visibility'] == Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE ||
                    $product['status'] != Mage_Catalog_Model_Product_Status::STATUS_ENABLED) {
                $deleteOnly = 1;
            } else {
                $deleteOnly = 0;
            }

            if($product['store_id'] && $product['store_id'] != Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID) {
                $cores = $this->getHelper()->getCoresByStoreId($product['store_id']);
                if (is_array($cores) && !empty($cores)) {
                    foreach ($cores as $core) {
                        $item = clone $rawItem;
                        $item->setProductId($product['id']);
                        $item->setCoreName($core);
                        $item->setStoreId($product['store_id']);
                        $item->setDeleteOnly($deleteOnly);
                        $item->setStatus(Zolago_Solrsearch_Model_Queue_Item::STATUS_WAIT);
                        $queueItems[$product['id'] . '_' . $core . '_' . $product['store_id'] . '_' . $deleteOnly] = $item;
                    }
                }
            }
        }

        $this->getQueue()->pushMultiple($queueItems);
    }

    /**
     * @param array $storeIds
     * @return array
     */
    protected function _filterStoreIds(array $storeIds) {
        return array_intersect($storeIds,$this->getHelper()->getAvailableStores());
    }


    /**
     * @return array
     */
    protected function _getChangableCategoryAttributes() {
        return array("is_active", "name", "include_in_menu", "is_anchor");
    }


    /**
     * @return Mage_Catalog_Model_Product
     */
    protected function getTmpProduct() {
        if(!$this->_tmpProduct) {
            $this->_tmpProduct = Mage::getModel("catalog/product");
        }
        return $this->_tmpProduct;
    }

    public function handleCatalogLayoutRender($observer)
    {
        if(Mage::getModel('zolagosolrsearch/catalog_product_list')->getMode() === Zolago_Solrsearch_Model_Catalog_Product_List::MODE_CATEGORY) {

            $replaceCatalogLayerNavigation = (int) Mage::Helper('solrsearch')->getSetting('replace_catalog_layer_nav');
            if ($replaceCatalogLayerNavigation > 0)
            {
                $layoutUpdate = Mage::getSingleton('core/layout')->getUpdate();
                if ($category = Mage::registry('current_category') && !Mage::registry('current_product'))
                {
                    $layoutUpdate->addHandle('solrbridge_solrsearch_category_view');
                }
            }
        }
    }
    public function checkRelatedCategoryProducts($observer) {
        $category = $observer->getEvent()->getCategory();
        $ids = $category->getRelatedProductsToRebuild();
        if (empty($ids)) {
            return;
        }

        $this->collectProducts($ids);
        $this->processCollectedProducts();
    }

    /**
     * @return Zolago_Solrsearch_Helper_Data
     */
    public function getHelper() {
        if(!$this->_helper) {
            $this->_helper = Mage::helper("zolagosolrsearch");
        }
        return $this->_helper;
    }
}
