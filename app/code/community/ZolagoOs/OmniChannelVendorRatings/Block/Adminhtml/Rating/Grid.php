<?php

class ZolagoOs_OmniChannelVendorRatings_Block_Adminhtml_Rating_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('ratingsGrid');
        $this->setDefaultSort('rating_code');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('rating/rating')
            ->getResourceCollection()
            ->addEntityFilter(Mage::registry('entityId'));
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('rating_id', array(
            'header'    => Mage::helper('rating')->__('ID'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'rating_id',
        ));

        $this->addColumn('rating_code', array(
            'header'    => Mage::helper('rating')->__('Rating Name'),
            'align'     =>'left',
            'index'     => 'rating_code',
        ));

        $this->addColumn('is_aggregate', array(
            'header'    => Mage::helper('rating')->__('Is Aggregatable'),
            'align'     =>'center',
            'index'     => 'is_aggregate',
            'type' => 'options',
            'options' => Mage::getSingleton('udropship/source')->setPath('yesno')->toOptionHash(),
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}
