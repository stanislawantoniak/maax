<?php

class GH_Statements_Block_Adminhtml_Calendar_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('ghstatements_calendar_grid');
        $this->setDefaultSort('calendar_id');
        $this->setDefaultDir('desc');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('ghstatements/calendar_collection');
        /* @var $collection GH_Statements_Model_Resource_Calendar_Collection */

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn("id", array(
            "index" => "calendar_id",
            "header" => Mage::helper("ghstatements")->__("ID"),
            "align" => "right",
            "type" => "number",
            "width" => "100px"
        ));
        $this->addColumn("name", array(
            "index" => "name",
            "header" => Mage::helper("ghstatements")->__("Calendar name"),
        ));
        
        $this->addColumn('action',
            array(
                'header'    => Mage::helper('catalog')->__('Action'),
                'type'      => 'action',
                'getter'     => 'getId',
                'width' 	=> '50px',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('catalog')->__('Edit'),
                        'field'   => 'id',
                        'url' => array ('base' => '*/*/calendar_edit'),
                    )
                ),
                'filter'    => false,
                'sortable'  => false
        ));

        return parent::_prepareColumns();
    }


    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/calendar_item', array('id' => $row->getId()));
    }
    
    public function getEditParamsForAssociated()
    {
        return array(
            'base'      =>  '*/*/calendar_edit',
            'params'    =>  array(
                'required' => $this->_getRequiredAttributesIds(),
                'popup'    => 1,
                'product'  => $this->_getProduct()->getId()
            )
        );
    }


}