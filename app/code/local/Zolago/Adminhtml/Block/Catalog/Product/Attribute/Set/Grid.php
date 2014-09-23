<?php

/**
 * description
 *
 * @category   Zolago
 * @package    Zolago_Adminhtml
 */
class Zolago_Adminhtml_Block_Catalog_Product_Attribute_Set_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('setGrid');
        $this->setDefaultSort('set_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter(Mage::registry('entityType'));

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        /*$this->addColumn('set_id', array(
            'header'    => Mage::helper('catalog')->__('ID'),
            'align'     => 'right',
            'sortable'  => true,
            'width'     => '50px',
            'index'     => 'attribute_set_id',
        ));*/

        $this->addColumn('set_name', array(
            'header'    => Mage::helper('catalog')->__('Set Name'),
            'align'     => 'left',
            'sortable'  => true,
            'index'     => 'attribute_set_name',
        ));
        $this->addColumn(
            'use_to_create_product',
            array(
                 'header'   => Mage::helper('catalog')->__('Use to create product'),
                 'align'    => 'left',
                 'sortable' => true,
                 'index'    => 'use_to_create_product',
                 'type'     => 'options',
                 'options'  => array(0 => Mage::helper('catalog')->__('No'), 1 => Mage::helper('catalog')->__('Yes'))
            )
        );
        $this->addColumn(
            'use_sizebox_list',
            array(
                'header' => Mage::helper('catalog')->__('Use sizebox list'),
                'align'    => 'left',
                'sortable' => true,
                'index'    => 'use_sizebox_list',
                'type'     => 'options',
                'options'  => array(0 => Mage::helper('catalog')->__('No'), 1 => Mage::helper('catalog')->__('Yes'))
            )
        );
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id'=>$row->getAttributeSetId()));
    }

}
