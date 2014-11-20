<?php
/**
 * brand settings grid
 */
class Zolago_Sizetable_Block_Adminhtml_Dropship_Settings_Grid_Brand extends Mage_Adminhtml_Block_Widget_Grid {
    const BRAND_ATTRIBUTE_ID = 81;
    public function __construct()
    {
        parent::__construct();
        $this->setId('connect_brand');
        $this->setDefaultSort('value');
        $this->setUseAjax(true);
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
                             'align'         => 'right',
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
        $collection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                      ->setAttributeFilter(self::BRAND_ATTRIBUTE_ID)
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
        return $this->getUrl('sizetable/index/brand', array('_current'=>true));
    }

    /**
     *
     * @return array
     */
    protected function _getSelectedBrand() {
        $brands = $this->getRequest()->getPost('selected_brand');
        if (is_null($brands)) {
            $vendorId = $this->getVendorId();
            $collection = Mage::getModel('zolagosizetable/vendor_brand')->getCollection();
            $collection->getSelect()
            ->columns(array('brand_id'))
            ->where('main_table.vendor_id = '.$vendorId);

            $brands = array();
            foreach ($collection as $brand) {
                var_dump($brand);
                die();
            }
            return array_keys($brands);
        }
        return $brands;

    }

}