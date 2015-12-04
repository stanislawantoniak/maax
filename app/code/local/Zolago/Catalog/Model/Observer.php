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
			if($category && $category->getId()){
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
}