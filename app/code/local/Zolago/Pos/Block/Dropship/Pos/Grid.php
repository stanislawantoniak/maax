<?php

class Zolago_Pos_Block_Dropship_Pos_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('zolagopos_pos_grid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('desc');
        // Need
        $this->setGridClass('z-grid');
        $this->setTemplate("zolagopos/pos/grid.phtml");
    }

    protected function _prepareCollection()
    {
        $vendor = Mage::getSingleton('udropship/session')->getVendor();
        /* @var $vendor ZolagoOs_OmniChannel_Model_Vendor */
        $collection = Mage::getResourceModel("zolagopos/pos_collection");
        /* @var $collection Zolago_Pos_Model_Resource_Pos_Collection */
        $collection->addVendorFilter($vendor);
        $collection->addAccountField();
        $collection->setOrder("priority", "DESC");
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getIndex() == "my_dhl_account") {
            $this->getCollection()->getSelect()->
            where("IF(use_dhl=1,dhl_account,'') like ?", '%' . $column->getFilter()->getValue() . '%');
            return $this;
        }
        return parent::_addColumnFilterToCollection($column);
    }

    protected function _prepareColumns()
    {
        $_helper = Mage::helper("zolagopos");

        $this->addColumn("name", array(
            "type" => "text",
            "index" => "name",
            "class" => "form-controll",
            "header" => $_helper->__("POS Name"),
        ));
        $this->addColumn("city", array(
            "type" => "text",
            "index" => "city",
            "class" => "form-control",
            "header" => $_helper->__("City"),
        ));
        $this->addColumn("is_available_as_pickup_point", array(
            "type" => "options",
            "options" => array(
                1 => $this->__("Yes"),
                0 => $this->__("No")
            ),
            "index" => "is_available_as_pickup_point",
            "class" => "form-control",
            "header" => $_helper->__("Available as Pick-Up Point"),
        ));
        $this->addColumn("show_on_map", array(
            "type" => "options",
            "options" => array(
                1 => $this->__("Yes"),
                0 => $this->__("No")
            ),
            "index" => "show_on_map",
            "class" => "form-control",
            "header" => $_helper->__("Show POS on map"),
        ));
        $this->addColumn("dhl_account", array(
            "type" => "text",
            "index" => "my_dhl_account",
            "class" => "form-control",
            "header" => $_helper->__("Dhl account"),
        ));
        $this->addColumn("is_active", array(
            "type" => "options",
            "options" => array(
                Zolago_Pos_Model_Pos::STATUS_ACTIVE => $this->__("Active"),
                Zolago_Pos_Model_Pos::STATUS_INACTIVE => $this->__("Not Active")
            ),
            "index" => "is_active",
            "class" => "form-control",
            "header" => $_helper->__("Is active"),
        ));

        $this->addColumn("priority", array(
            "type" => "number",
            "index" => "priority",
            "class" => "form-control",
            "header" => $_helper->__("Priority"),
        ));

        $this->addColumn("minimal_stock", array(
            "type" => "number",
            "index" => "minimal_stock",
            "class" => "form-control",
            "header" => $_helper->__("Minimal stock"),
        ));
        $this->addColumn("actions", array(
            'header' => $_helper->__('Action'),
            'renderer' => Mage::getConfig()->getBlockClassName("zolagoadminhtml/widget_grid_column_renderer_link"),
            'width' => '50px',
            'type' => 'action',
            'index' => 'pos_id',
            'link_action' => "*/*/edit",
            'link_param' => 'pos_id',
            'link_label' => $_helper->__('Edit'),
            'link_target' => '_self',
            'filter' => false,
            'sortable' => false
        ));


        return parent::_prepareColumns();
    }


    public function getRowUrl($item)
    {
        return null;
    }

}