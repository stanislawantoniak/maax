<?php

/**
 * Class GH_FeedExport_Model_Observer
 */
class GH_FeedExport_Model_Observer
{

    public function addProductPreFilters($observer)
    {

        $event = $observer->getEvent();

        if ($event->getName() !== "adminhtml_promo_catalog_edit_tab_main_prepare_form")
            return;

        $model = Mage::registry('current_model');

        $form = $observer->getForm();
        $filters = $form->addFieldset('filters', array('legend' => Mage::helper('catalog')->__('Product Pre-Filters')));

        $filters->addField('product_status', 'select', array(
            'label' => Mage::helper('catalog')->__('Status'),
            'required' => false,
            'name' => 'product_status',
            'value' => $model->getProductStatus(),
            'values' => Mage::getSingleton('catalog/product_status')->getAllOption()
        ));

        $filters->addField('product_visibility', 'select', array(
            'label' => Mage::helper('catalog')->__('Visibility'),
            'required' => false,
            'name' => 'product_visibility',
            'value' => $model->getProductVisibility(),
            'values' => Mage::getModel('catalog/product_visibility')->getAllOption()
        ));

        $filters->addField('product_type_id', 'select', array(
            'label' => Mage::helper('catalog')->__('Type'),
            'required' => false,
            'name' => 'product_type_id',
            'value' => $model->getData("product_type_id"),
            'values' => Mage::getSingleton('catalog/product_type')->getAllOption()
        ));


        $filters->addField('product_inventory_is_in_stock', 'select', array(
            'label' => Mage::helper('catalog')->__('Stock Availability'),
            'name' => 'product_inventory_is_in_stock',
            'values' => array(
                array('value' => '', 'label' => ''),
                array('value' => 1, 'label' => Mage::helper('catalog')->__('In Stock')), //Mage_CatalogInventory_Model_Stock::STOCK_IN_STOCK
                array('value' => 2, 'label' => Mage::helper('catalog')->__('Out of Stock')) //Mage_CatalogInventory_Model_Stock::STOCK_OUT_OF_STOCK
            ),
            'value' => $model->getData("product_inventory_is_in_stock")
        ));

    }
}