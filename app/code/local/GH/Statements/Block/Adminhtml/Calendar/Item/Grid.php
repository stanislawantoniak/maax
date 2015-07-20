<?php

class GH_Statements_Block_Adminhtml_Calendar_Item_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('ghstatements_calendar_item_grid');
        $this->setDefaultSort('item_id');
        $this->setDefaultDir('desc');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('ghstatements/calendar_item_collection');
        $collection->addFieldToFilter('calendar_id',$this->getRequest()->get('id'));
        /* @var $collection GH_Statements_Model_Resource_Calendar_Item_Collection */

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn("id", array(
            "index" => "item_id",
            "header" => Mage::helper("ghstatements")->__("ID"),
            "align" => "right",
            "type" => "number",
            "width" => "40px",
            "sortable" => false,
            "filter" => false,
        ));
        $this->addColumn("date", array(
            "index" => "event_date",
            "header" => Mage::helper("ghstatements")->__("Event date"),
        ));

        return parent::_prepareColumns();
    }
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/calendar_item_edit', array('id' => $row->getId()));
    }

}