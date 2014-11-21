<?php
/**
 * brand settings grid
 */
class Zolago_Sizetable_Block_Adminhtml_Dropship_Settings_Brand_Grid extends Mage_Adminhtml_Block_Widget_Grid {
    public function __construct()
    {
        parent::__construct();
        $this->setId('connect_brand');
        $this->setDefaultSort('value');
        $this->setUseAjax(true);
    }
    
    /**
     * brand id
     * @return int
     */
    protected function _getBrandId() {
        $attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product','manufacturer');
        return $attribute->getId();
    }
    
    /**
     * two columns: checkbox and brand name
     */

    protected function _prepareColumns() {
        $this->addColumn('connect_vendor_brand', array(
                             'header_css_class' => 'a-center',
                             'type'      => 'checkbox',
                             'name'		 => 'connect_vendor_brand',
                             'values'    => $this->_getSelectedBrand(),
                             'align'     => 'center',
                             'width'         => '50px',
                             'index'     => 'option_id'
                         ));

        $this->addColumn('value', array(
                             'header'        => Mage::helper('zolagosizetable')->__('Brand'),
                             'align'         => 'left',
                             'index'         => 'value',
                         ));

        parent::_prepareColumns();
    }

    protected function _addColumnFilterToCollection($column)
    {
        $id = $column->getId();
        if ($id == 'connect_vendor_brand') {
            $select = $this->getCollection()->getSelect();
            if ($column->getFilter()->getValue()) {
                $select->join(
                    array('zvb' => Mage::getSingleton('core/resource')->getTableName('zolagosizetable/vendor_brand')),
                    'main_table.option_id = zvb.brand_id AND zvb.vendor_id = '.$this->getVendorId());
            } else {
                $select->joinLeft(
                    array('zvb' => Mage::getSingleton('core/resource')->getTableName('zolagosizetable/vendor_brand')),
                    'main_table.option_id = zvb.brand_id AND zvb.vendor_id = '.$this->getVendorId())
                    ->where('zvb.brand_id is null');
            }
            return $this;
        }
        parent::_addColumnFilterToCollection($column);
        return $this;
    }
    
    protected function _prepareCollection()
    {
        $this->setDefaultFilter(array('connect_vendor_brand'=>1));
        $collection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                      ->setAttributeFilter($this->_getBrandId())
                      ->setStoreFilter(0, false);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * 
     * @return string
     */

    public function getGridUrl()
    {
        return $this->getUrl('sizetableadmin/index/brand', array('_current'=>true));
    }

    /**
     *
     * @return array
     */
    protected function _getSelectedBrand() {
        $json = $this->getRequest()->getPost('selected_brand');
        if (is_null($json)) {
            $vendorId = $this->getVendorId();
            $collection = Mage::getModel('zolagosizetable/vendor_brand')->getCollection();
            $collection->getSelect()
            ->columns(array('brand_id'))
            ->where('main_table.vendor_id = '.$vendorId);

            $brands = array();
            foreach ($collection as $brand) {
                $brands[] = $brand->getData('brand_id');
            }
        } else {
            $brands = array_keys((array)Zend_Json::decode($json));
        }
        return $brands;

    }

}