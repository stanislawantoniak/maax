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
        Mage::log(microtime() . " Starting processConfigurableQueue ", 0, 'configurable_update.log');
        //Mage::getResourceModel('zolagocatalog/queue_configurable')->clearQueue();
        Mage::getModel('zolagocatalog/queue_configurable')->process(2500);
    }


    /**
     * Process price type queue
     */
    public static function processPriceTypeQueue()
    {
        //Mage::helper('zolagocatalog/pricetype')->_logQueue("Clear queue");
        Mage::getResourceModel('zolagocatalog/queue_pricetype')->clearQueue();
        //Mage::helper('zolagocatalog/pricetype')->_logQueue("Start process");
        $process = Mage::getModel('zolagocatalog/queue_pricetype')->process(10);
        //Mage::helper('zolagocatalog/pricetype')->_logQueue("Products processed {$process}");
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
        if ($attributesAffected) {
            //Mage::helper('zolagocatalog/pricetype')->_log("{$productId} Converter price type attributes affected");
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
        $priceMargin = isset($attributesData['price_margin']) ? $attributesData['price_margin'] : 0;

        $productIdsLog = implode(",", $productIds);
        if (!empty($converterPriceType) || !empty($priceMargin)) {
//            Mage::helper('zolagocatalog/pricetype')->_log(
//                "{$productIdsLog} Converter price type attributes affected: converterPriceType - {$converterPriceType}, priceMargin: {$priceMargin}"
//            );
            //Add to queue
            Zolago_Catalog_Helper_Pricetype::queue($productIds);
        }
    }
}