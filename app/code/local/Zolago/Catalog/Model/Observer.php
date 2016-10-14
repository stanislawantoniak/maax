<?php
/**
 * Class Zolago_Catalog_Model_Observer
 *
 * @category    Zolago
 * @package     Zolago_Catalog
 *
 */
class Zolago_Catalog_Model_Observer
{
    /**
     * Handle default category on product page
     * @area: frontend
     * @event: catalog_controller_product_init
     * @param Varien_Event_Observer $observer
     */
    public function productInit(Varien_Event_Observer $observer) {
        $product = $observer->getEvent()->getProduct();
        /* @var $product Mage_Catalog_Model_Products */

        // No category id
        //if(!$product->getCategory()){
        $rootId = Mage::helper("zolagosolrsearch")->getRootCategoryId();
        $category = Mage::helper("zolagosolrsearch")->getDefaultCategory($product, $rootId);
        /* @var $category Mage_Catalog_Model_Category */
        if($category && $category->getId()) {
            $product->setCategory($category);
            Mage::unregister('current_category');
            Mage::register('current_category', $category);
        }
        //}
    }

    public function addColumnWidthField(Varien_Event_Observer $observer)
    {
        $fieldset = $observer->getForm()->getElement('front_fieldset');
        $fieldset->addField('column_width', 'text', array(
                                'name' => 'column_width',
                                'label' => Mage::helper('catalog')->__('Column width (px)'),
                                'title' => Mage::helper('catalog')->__('Column width (px)'),
                                'class' => 'validate-digits',
                            ));
    }
    public function addColumnAttributeOrder(Varien_Event_Observer $observer)
    {
        $fieldset = $observer->getForm()->getElement('front_fieldset');
        $fieldset->addField('column_attribute_order', 'text', array(
                                'name' => 'column_attribute_order',
                                'label' => Mage::helper('catalog')->__('Attribute order'),
                                'title' => Mage::helper('catalog')->__('Attribute order'),
                                'class' => 'validate-digits',
                            ));
    }

    static public function processConfigurableQueue()
    {
        Mage::getModel('zolagocatalog/queue_configurable')->process(6000);
    }


    /**
     * Process price type queue
     */
    public static function processPriceTypeQueue()
    {
        Mage::getResourceModel('zolagocatalog/queue_pricetype')->clearQueue();
        Mage::getModel('zolagocatalog/queue_pricetype')->process(2000);
    }

    static public function clearConfigurableQueue()
    {
        Mage::getResourceModel('zolagocatalog/queue_configurable')->clearQueue();
    }

    /**
     * After added/update a product
     *
     * @param Varien_Event_Observer $observer
     */
    public function productAfterUpdate($observer)
    {
        $product = $observer->getProduct();
        $productId = $product->getId();

        $attributesAffected = false;

        //Price should be switched/saved/calculated only if are different
        if ($product->dataHasChangedFor(Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_CONVERTER_PRICE_TYPE_CODE)) {
            $attributesAffected = true;
        }

        if ($product->dataHasChangedFor(Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_PRICE_MARGIN_CODE)) {
            $attributesAffected = true;
        }
        if ($product->dataHasChangedFor(Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_CONVERTER_MSRP_TYPE_CODE)) {
            $attributesAffected = true;
        }
        if ($attributesAffected) {
            //Add to queue
            Zolago_Catalog_Helper_Pricetype::queueProduct($productId);
            //------Add to queue
        }
    }

    /**
     * After attribute changed
     *
     * @param Varien_Event_Observer $observer
     */
    public function productAttributeMassUpdate($observer)
    {
        $productIds = $observer->getData('product_ids');
        $attributesData = $observer->getData('attributes_data');

        $converterPriceType = isset($attributesData['converter_price_type']) ? $attributesData['converter_price_type']
                              : 0;
        $priceMargin = isset($attributesData['price_margin']) ? $attributesData['price_margin'] : null;
        $msrpType = (isset($attributesData['converter_msrp_type']) && $attributesData['converter_msrp_type'] == 0)? 1:0;

        if (!empty($converterPriceType) || !is_null($priceMargin) || !empty($msrpType)) {
            //Add to queue
            Zolago_Catalog_Helper_Pricetype::queue($productIds);
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     * @throws Exception
     */
    public function catalogProductDeleteAfter(Varien_Event_Observer $observer)
    {
        try {
            $product = $observer->getEvent()->getProduct();
            $productId = $product->getId();

            $resource = Mage::getSingleton('core/resource');
            $writeConnection = $resource->getConnection('core_write');
            $tableName = $resource->getTableName('catalog_category_product');
            $query = "DELETE FROM {$tableName} WHERE product_id = " . (int)$productId;
            $writeConnection->query($query);
        } catch (Mage_Adminhtml_Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }

    /**
     * Add 'attribute_base_store' into Admin Store View Information edit form
     * If attribute_base_store will be specified labels will be taken if not present.
     * If you do not specify a store view then the default (Admin) labels will be used
     * Event: adminhtml_store_edit_form_prepare_form
     * @see Mage_Adminhtml_Block_System_Store_Edit_Form::_prepareForm()
     *
     * @param Varien_Event_Observer $observer
     */
    public function addFieldsToAdminStoreViewEdit($observer) {
        /** @var Zolago_Catalog_Helper_Data $hlp */
        $hlp = Mage::helper('zolagocatalog');
        /** @var Mage_Adminhtml_Block_System_Store_Edit_Form $block */
        $block = $observer->getData('block');
        $form = $block->getForm();
        $fieldset = $form->getElements()->searchById('store_fieldset');

        if (Mage::registry('store_type') == 'store') {
            $storeModel = Mage::registry('store_data');
            if ($postData = Mage::registry('store_post_data')) {
                $storeModel->setData($postData['store']);
            }

            $fieldset->addField('store_attribute_base_store', 'select', array(
                                    'name'      => 'store[attribute_base_store]',
                                    'label'     => $hlp->__('Use attributes labels from'),
                                    'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(true, false),
                                    'value'     => $storeModel->getAttributeBaseStore(),
                                    'note'      => $hlp->__('From this store view labels will be taken if not present. If you do not specify a store view then the default (Admin) labels will be used')
                                ));
            $fieldset->addField('store_virtual_root_category', 'text', array(
                'name'      => 'store[virtual_root_category]',
                'label'     => $hlp->__('Virtual root category name'),
                'value'     => $storeModel->getVirtualRootCategory(),
                'required'  => false,
                'disabled'  => $storeModel->isReadOnly(),
                'note' 	    => $hlp->__('This name will be used in hamburger menu as root category name')
            ));

        }
    }

    /**
     * update url after assign website
     */
    public function catalogProductWebsiteUpdate($observer) {
        $event = $observer->getEvent();
        $productIds = $event->getProductIds();
        $websites = $event->getWebsiteIds();
        $action = $event->getAction();

        $catalogResource = Mage::getResourceModel('catalog/product');

        $url = Mage::getSingleton('catalog/url');
        $url->setShouldSaveRewritesHistory(true);
        $resource = $url->getResource();

        $keys = $catalogResource->getUrlKeysByStore($productIds);


        $urlRewriteCollection = Mage::getModel('core/url_rewrite')->getCollection()
                                ->addFieldToFilter('product_id', array('in' =>  $productIds));
        $urlList = array();
        foreach ($urlRewriteCollection as $itemUrl) {
            $urlList[$itemUrl->getProductId()][$itemUrl->getStoreId()] = $itemUrl->getRequestPath();
        }


        foreach($productIds as $id) {
            $refresh = false;
            foreach ($websites as $wid) {
                $storeIds = Mage::app()->getWebsite($wid)->getStoreIds();
                foreach($storeIds as $sId) {
                    switch ($action) {
                    case 'add':
                        if ($key = empty($keys[$id][$sId])? (empty($keys[$id][0])? '':$keys[$id][0]):$keys[$id][$sId]) {
                            if (empty($urlList[$id][$sId]) || ($urlList[$id][$sId] != $key)) {
                                $model = Mage::getModel('catalog/product')->setStoreId($sId)->load($id);
                                $model->setData('store_id', $sId); // Trick
                                $model->setUrlKey($key);
                                $resource->saveProductAttribute($model, 'url_key');
                                $refresh = true;
                            }
                        }
                        break;
                    case 'remove':
                        if (!empty($urlList[$id][$sId])) {
                            $resource->clearProductRewrites($id, $sId);
                            $refresh = true;
                        }
                        break;
                    }
                }
            }
            if ($refresh) {
                $url->refreshProductRewrite($id);
            }
        }
    }
}