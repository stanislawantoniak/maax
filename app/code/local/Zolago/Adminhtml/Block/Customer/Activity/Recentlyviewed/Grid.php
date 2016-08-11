<?php

/**
 * Class Zolago_Adminhtml_Block_Customer_Activity_Login_Grid
 */
class Zolago_Adminhtml_Block_Customer_Activity_Recentlyviewed_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('Customer_Activity_Recentlyviewed_Grid');
        $this->setDefaultSort('added_at');
    }

    protected function _prepareCollection() {
        /** @var Mage_Reports_Model_Resource_Product_Index_Viewed_Collection $collection */
        $collection = Mage::getResourceModel('reports/product_index_viewed_collection');
        $collection->setCustomerId($this->getCustomerId());
        $collection->addUrlRewrite();
        $collection->addIndexFilter();
        $collection->joinAttribute(
            'name',
            'catalog_product/name',
            'entity_id',
            null,
            'left',
            0
        );
        $collection->joinAttribute(
            'udropship_vendor',
            'catalog_product/udropship_vendor',
            'entity_id',
            null,
            'left',
            0
        );
        // Branshop attribute
        $collection->joinAttribute(
            'brandshop',
            'catalog_product/brandshop',
            'entity_id',
            null,
            'left',
            0
        );

        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns() {
        /** @var Zolago_Catalog_Helper_Data $helper */
        $helper = Mage::helper("zolagocatalog");

        // Name
        $this->addColumn('name', array(
            'header' => $helper->__('Name'),
            'index'  => 'name',
        ));
        // Last viewed
        $this->addColumn('added_at', array(
            'header'  => Mage::helper("zolagolog")->__('Last viewed'),
            'index'   => 'added_at',
            'type'    => 'datetime'
        ));
        // Vendor
        $this->addColumn('udropship_vendor', array(
            'header'   => $helper->__('Vendor'),
            'index'    => 'udropship_vendor',
            'type'     => 'options',
            'options'  => $this->_getAttributeOptions('udropship_vendor'),
            'renderer' => Mage::getConfig()->getBlockClassName("zolagoadminhtml/widget_grid_column_renderer_vendorName"),
        ));
        // Branshop attribute
        $this->addColumn('brandshop',
            array(
                'header'  => $helper->__('Brandshop'),
                'index'   => 'brandshop',
                'type'    => 'options',
                'options' => $this->_getAttributeOptions('brandshop')
            ));

        // Show
        $this->addColumn('action',
            array(
                'header'  => Mage::helper('catalog')->__('Action'),
                'width'   => '50px',
                'type'    => 'action',
                'getter'  => 'getId',
                'actions' => array(
                    array(
                        'caption' => Mage::helper('catalog')->__('Show'),
                        'url'     => array(
                            'base'  => '*/catalog_product/edit',
                        ),
                        'field'   => 'id'
                    )
                ),
                'filter'   => false,
                'sortable' => false,
            ));

        return parent::_prepareColumns();
    }

    /**
     * @return int
     * @throws Exception
     */
    protected function getCustomerId() {
        return (int)$this->getRequest()->getParam('id');
    }

    /**
     * @param $attribute_code
     * @return array
     */
    protected function _getAttributeOptions($attribute_code) {
        $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', $attribute_code);
        $options = array();
        foreach ($attribute->getSource()->getAllOptions(false, true) as $option) {
            $options[$option['value']] = $option['label'];
        }
        return $options;
    }

    /**
     * @param $row
     * @return string
     */
    public function getRowUrl($row) {
        return $this->getUrl('*/catalog_product/edit', array('id' => $row->getProductId()));
    }
}
