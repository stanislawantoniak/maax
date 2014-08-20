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
            'title' => Mage::helper('catalog')->__('Column width (px)')
        ));
    }

    static public function processConfigurableQueue()
    {
        Mage::log(microtime() . " Starting processConfigurableQueue ", 0, 'configurable_update.log');
        Mage::getResourceModel('zolagocatalog/queue_configurable')->clearQueue();
        Mage::getModel('zolagocatalog/queue_configurable')->process(2000);
    }

    /**
     * Process price type queue
     */
    public static function processPriceTypeQueue()
    {
        Mage::helper('zolagocatalog/pricetype')->_logQueue("Clear queue");
        Mage::getResourceModel('zolagocatalog/queue_pricetype')->clearQueue();
        Mage::helper('zolagocatalog/pricetype')->_logQueue("Start process");
        $process = Mage::getModel('zolagocatalog/queue_pricetype')->process(2000);
        Mage::helper('zolagocatalog/pricetype')->_logQueue("Products processed {$process}");
    }

    static public function clearConfigurableQueue()
    {
        Mage::getResourceModel('zolagocatalog/queue_configurable')->clearQueue();
    }
}