<?php
/**
 * attribute set settings grid
 */
class Zolago_Sizetable_Block_Adminhtml_Dropship_Settings_Grid_Attributeset extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct()
    {
        parent::__construct();
        $this->setId('connect_attributeset');
        $this->setDefaultSort('attribute_set_name');
        $this->setUseAjax(true);
    }
    protected function _prepareColumns() {
        $this->addColumn('connect_vendor_attributeset', array(
                             'header_css_class' => 'a-center',
                             'type'      => 'checkbox',
                             'name'      => 'connect_vendor_attribute',
                             'values'    => $this->_getSelectedAttributeSet(),
                             'align'     => 'center',
                             'width'         => '50px',
                             'index'     => 'set_id'
                         ));

        $this->addColumn('value', array(
                             'header'        => Mage::helper('zolagosizetable')->__('Attribute set'),
                             'align'         => 'right',
                             'index'         => 'attribute_set_name',
                         ));

        parent::_prepareColumns();
    }
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('eav/entity_attribute_set')->getCollection()
                ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getEntityType()->getId());
        $collection->getSelect()
                    ->columns('main_table.attribute_set_id as set_id');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _addColumnFilterToCollection($column)
    {
        $id = $column->getId();
        if ($id == 'connect_vendor_attributeset') {
            $select = $this->getCollection()->getSelect();
            if ($column->getFilter()->getValue()) {
                $select->join(
                    array('zvb' => Mage::getSingleton('core/resource')->getTableName('zolagosizetable/vendor_attribute_set')),
                    'main_table.attribute_set_id = zvb.attribute_set_id AND zvb.vendor_id = '.$this->getVendorId());
            } else {
                $select->joinLeft(
                    array('zvb' => Mage::getSingleton('core/resource')->getTableName('zolagosizetable/vendor_attribute_set')),
                    'main_table.attribute_set_id = zvb.attribute_set_id AND zvb.vendor_id = '.$this->getVendorId())
                    ->where('zvb.attribute_set_id is null');
            }
            return $this;
        }
        parent::_addColumnFilterToCollection($column);
        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('sizetable/index/attributeset', array('_current'=>true));
    }

    /**
     *
     * @return array
     */
    protected function _getSelectedAttributeSet() {	
        return array();
        $sets = $this->getRequest()->getPost('selected_sets');
        if (is_null($sets)) {
            $vendorId = $this->getVendorId();
            $collection = Mage::getModel('zolagosizetable/vendor_attributeset')->getCollection();
            $collection->getSelect()
            ->columns(array('attribute_set_id'))
            ->where('main_table.vendor_id = '.$vendorId);

            $sets = array();
            foreach ($collection as $set) {
                var_dump($set);
                die();
            }
            return array_keys($sets);
        }
        return $sets;

    }

}