<?php

/**
 * Class Zolago_Adminhtml_Block_Catalog_Product_Grid
 */
class Zolago_Adminhtml_Block_Catalog_Product_Grid extends Mage_Adminhtml_Block_Catalog_Product_Grid
{

    protected function _prepareCollection()
    {
        /** @var Mage_Catalog_Model_Resource_Product_Collection $collection */

        // ORIGINAL PART START
        $store = $this->_getStore();
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('attribute_set_id')
            ->addAttributeToSelect('type_id');

        if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory')) {
            $collection->joinField('qty',
                'cataloginventory/stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left');
        }
        if ($store->getId()) {
            //$collection->setStoreId($store->getId());
            $adminStore = Mage_Core_Model_App::ADMIN_STORE_ID;
            $collection->addStoreFilter($store);
            $collection->joinAttribute(
                'name',
                'catalog_product/name',
                'entity_id',
                null,
                'inner',
                $adminStore
            );
            $collection->joinAttribute(
                'custom_name',
                'catalog_product/name',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'status',
                'catalog_product/status',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'visibility',
                'catalog_product/visibility',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'price',
                'catalog_product/price',
                'entity_id',
                null,
                'left',
                $store->getId()
            );
        } else {
            $collection->addAttributeToSelect('price');
            $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
            $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
        }
        // ORIGINAL PART END

        // Get current store for joining attributes
        $store = $this->_getStore();
        if ($store->getId()) {
            $_store = $store->getId();
        } else {
            $_store = null;
        }

        // Vendor
        $collection->joinAttribute(
            'udropship_vendor',
            'catalog_product/udropship_vendor',
            'entity_id',
            null,
            'left',
            $_store
        );
        // Branshop attribute
        $collection->joinAttribute(
            'brandshop',
            'catalog_product/brandshop',
            'entity_id',
            null,
            'left',
            $_store
        );
        // Color attribute
        $collection->joinAttribute(
            'color',
            'catalog_product/color',
            'entity_id',
            null,
            'left',
            $_store
        );
        // Description status attribute
        $collection->joinAttribute(
            'description_status',
            'catalog_product/description_status',
            'entity_id',
            null,
            'left',
            $_store
        );

        // Finally
        $this->setCollection($collection);
        Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
        $collection->addWebsiteNamesToResult();
        return $this;
    }

    protected function _prepareColumns()
    {
        /** @var Zolago_Catalog_Helper_Data $helper */
        $helper = Mage::helper("zolagocatalog");

        // Vendor
        $this->addColumnAfter('udropship_vendor',
            array(
                'header' => $helper->__('Vendor'),
                'index' => 'udropship_vendor',
                'type' => 'options',
                'options' => $this->_getAttributeOptions('udropship_vendor'),
                'renderer' => Mage::getConfig()->getBlockClassName("zolagoadminhtml/widget_grid_column_renderer_vendorName"),
            ), 'status');
        // Branshop attribute
        $this->addColumnAfter('brandshop',
            array(
                'header' => $helper->__('Brandshop'),
                'index' => 'brandshop',
                'type' => 'options',
                'options' => $this->_getAttributeOptions('brandshop')
            ), 'udropship_vendor');
        // Color attribute
        $this->addColumnAfter('color',
            array(
                'header' => $helper->__('Color'),
                'index' => 'color',
                'type' => 'options',
                'options' => $this->_getAttributeOptions('color')
            ), 'brandshop');
        // Description status attribute
        $this->addColumnAfter('description_status',
            array(
                'header' => $helper->__('Description status'),
                'index' => 'description_status',
                'type' => 'options',
                'options' => $this->_getAttributeOptions('description_status')
            ), 'color');

        return parent::_prepareColumns();
    }

    protected function _getAttributeOptions($attribute_code)
    {
        $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', $attribute_code);
        $options = array();
        foreach ($attribute->getSource()->getAllOptions(false, true) as $option) {
            $options[$option['value']] = $option['label'];
        }
        return $options;
    }
}
