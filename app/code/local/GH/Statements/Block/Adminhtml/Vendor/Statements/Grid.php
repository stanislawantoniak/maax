<?php

class GH_Statements_Block_Adminhtml_Vendor_Statements_Grid extends Mage_Adminhtml_Block_Widget_Grid
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
        $collection = Mage::getResourceModel('ghstatements/statement_collection');
        /* @var $collection GH_Statements_Model_Resource_Calendar_Collection */

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn("id", array(
            "index" => "statement_id",
            "header" => Mage::helper("ghstatements")->__("ID"),
            "align" => "right",
            "type" => "number",
            "width" => "40px",
            "filter" => false,
            "sortable" => false,
        ));
        $this->addColumn("name", array(
            "index" => "name",
            "header" => Mage::helper("ghstatements")->__("Name"),
        ));
        $this->addColumn("vendor_id", array(
            "index" => "vendor_id",
            "header" => Mage::helper("ghstatements")->__("Vendor"),
        ));

        /*Values*/
        $this->addColumn("order_commission_value", array(
            "index" => "order_commission_value",
            "header" => Mage::helper("ghstatements")->__("Order Commission Amount"),
        ));
        $this->addColumn("order_value", array(
            "index" => "order_value",
            "header" => Mage::helper("ghstatements")->__("Order Amount"),
        ));
        $this->addColumn("rma_commission_value", array(
            "index" => "rma_commission_value",
            "header" => Mage::helper("ghstatements")->__("RMA Commission Amount"),
        ));
        $this->addColumn("rma_value", array(
            "index" => "rma_value",
            "header" => Mage::helper("ghstatements")->__("RMA Amount"),
        ));
        $this->addColumn("refund_value", array(
            "index" => "refund_value",
            "header" => Mage::helper("ghstatements")->__("Refund Amount"),
        ));
        /*Values*/


        /*Totals*/
        $this->addColumn("tracking_charge_subtotal", array(
            "index" => "tracking_charge_subtotal",
            "header" => Mage::helper("ghstatements")->__("Tracking Charge Subtotal (netto)"),
        ));
        $this->addColumn("tracking_charge_total", array(
            "index" => "tracking_charge_total",
            "header" => Mage::helper("ghstatements")->__("Tracking Charge Total"),
        ));
        /*Totals*/

        $this->addColumn('action',
            array(
                'header' => Mage::helper('ghstatements')->__('Action'),
                'width' => '50px',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => array(
                    array(
                        'caption' => Mage::helper('ghstatements')->__('Edit'),
                        'url' => array('base' => '*/vendor_statements/edit'),
                        'field' => 'id'
                    )
                ),
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'is_system' => true,
            ));

        return parent::_prepareColumns();
    }


    public function getRowUrl($row)
    {
        return $this->getUrl('*/vendor_statements/edit', array('id' => $row->getId()));
    }


}