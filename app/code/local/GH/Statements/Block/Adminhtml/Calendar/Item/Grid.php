<?php

class GH_Statements_Block_Adminhtml_Calendar_Item_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('ghstatements_calendar_item_grid');
        $this->setDefaultSort('date');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
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
            "align" => "left",
            "type" => "number",
            "width" => "5px",
            "sortable" => false,
            "filter" => false,
        ));
        $this->addColumn("date", array(
            "index" => "event_date",
            "header" => Mage::helper("ghstatements")->__("Event date"),
            "type" => "date",
            "width" => "500px",
        ));

        return parent::_prepareColumns();
    }
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/calendar_item_edit', array('id' => $row->getId()));
    }

}