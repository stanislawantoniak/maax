<?php

class GH_Statements_Block_Adminhtml_Vendor_Statements_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('ghstatements_statements_grid');
        $this->setDefaultSort('event_date');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        /* @var $collection GH_Statements_Model_Resource_Calendar_Collection */
        $collection = Mage::getResourceModel('ghstatements/statement_collection');

        $select = $collection->getSelect();
        $select->joinLeft(
            array('vendor' => Mage::getSingleton('core/resource')->getTableName('udropship/vendor')),
            'main_table.vendor_id = vendor.vendor_id',
            array("vendor_name"));

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn("id", array(
            "index" => "id",
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
        $this->addColumn("vendor_name", array(
            "index" => "vendor_name",
            "header" => Mage::helper("ghstatements")->__("Vendor"),
        ));
        $this->addColumn("event_date", array(
            "index" => "event_date",
            "header" => Mage::helper("ghstatements")->__("Event date"),
            "type" => "date",
        ));

        /*Values*/
        $this->addColumn("order_commission_value", array(
            "index" => "order_commission_value",
            "header" => Mage::helper("ghstatements")->__("Order: Commission Amount"),
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base')
        ));
        $this->addColumn("gallery_discount_value", array(
            "index" => "gallery_discount_value",
            "header" => Mage::helper("ghstatements")->__("Gallery Discount"),
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base')
        ));
        $this->addColumn("commission_correction", array(
            "index" => "commission_correction",
            "header" => Mage::helper("ghstatements")->__("Commission correction"),
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base')
        ));
        $this->addColumn("order_value", array(
            "index" => "order_value",
            "header" => Mage::helper("ghstatements")->__("Order: Amount"),
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base')
        ));
        $this->addColumn("rma_commission_value", array(
            "index" => "rma_commission_value",
            "header" => Mage::helper("ghstatements")->__("RMA: Commission Amount"),
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base')
        ));
        $this->addColumn("rma_value", array(
            "index" => "rma_value",
            "header" => Mage::helper("ghstatements")->__("RMA: Amount"),
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base')
        ));
        $this->addColumn("refund_value", array(
            "index" => "refund_value",
            "header" => Mage::helper("ghstatements")->__("Refund: Amount"),
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base')
        ));
        /*Values*/


        /*Totals*/
        $this->addColumn("tracking_charge_subtotal", array(
            "index" => "tracking_charge_subtotal",
            "header" => Mage::helper("ghstatements")->__("Tracking: Charge Subtotal"),
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base')
        ));
        $this->addColumn("tracking_charge_total", array(
            "index" => "tracking_charge_total",
            "header" => Mage::helper("ghstatements")->__("Tracking: Charge Total"),
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base')
        ));
        $this->addColumn("delivery_correction", array(
            "index" => "delivery_correction",
            "header" => Mage::helper("ghstatements")->__("Delivery correction"),
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base')
        ));
        /*Totals*/

        /*Marketing*/
        $this->addColumn("marketing_value", array(
            "index" => "marketing_value",
            "header" => Mage::helper("ghstatements")->__("Marketing: Amount"),
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base')
        ));
        $this->addColumn("marketing_correction", array(
            "index" => "marketing_correction",
            "header" => Mage::helper("ghstatements")->__("Marketing correction"),
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base')
        ));
        /*Marketing*/
        /* commissions */
        $this->addColumn("total_commssion_netto", array(
            "index" => "total_commission_netto",
            "header" => Mage::helper("ghstatements")->__("Commissions netto"),
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base')
        ));
        $this->addColumn("total_commssion", array(
            "index" => "total_commission",
            "header" => Mage::helper("ghstatements")->__("Commissions brutto"),
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base')
        ));
        
        /* commissions */
        /*to pay*/
        $this->addColumn("to_pay", array(
            "index" => "to_pay",
            "header" => Mage::helper("ghstatements")->__("To payout"),
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base')
        ));
        $this->addColumn("last_statement_balance", array(
            "index" => "last_statement_balance",
            "header" => Mage::helper("ghstatements")->__("Last balance"),
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base')
        ));
        /*to pay*/
        /*Payment*/
        $this->addColumn("payment_value", array(
            "index" => "payment_value",
            "header" => Mage::helper("ghstatements")->__("Payment: Amount"),
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base')
        ));
        /*Payment*/

        $this->addColumn('edit',
            array(
                'header' => Mage::helper('ghstatements')->__('Edit'),
                'width' => '50px',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => array(
                    array(
                        'caption' => Mage::helper('ghstatements')->__('Edit'),
                        'url' => array('base' => '*/vendor_statements/edit'),
                        'field' => 'id'
                    ),
                ),
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'is_system' => true,
            ));
        $this->addColumn('delete',
            array(
                'header' => Mage::helper('ghstatements')->__('Delete'),
                'width' => '50px',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => array(
                    array(
                        'caption' => Mage::helper('ghstatements')->__('Delete'),
                        'url' => array('base' => '*/vendor_statements/delete'),
                        'field' => 'id',
                        'confirm'  => Mage::helper('catalog')->__('Are you sure?')
                    ),
                ),
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'is_system' => true,
            ));

        return parent::_prepareColumns();
    }

	protected function _prepareMassaction()
	{
		$this->setMassactionIdField('id');
		$this->getMassactionBlock()->setFormFieldName('vendor_statements');

		$this->getMassactionBlock()->addItem('invoices', array(
			'label'=> Mage::helper("ghstatements")->__('Generate invoices'),
			'url'  => $this->getUrl('*/*/massInvoice'),
			'confirm' => Mage::helper("ghstatements")->__('Do you want to generate invoices for selected statements?')
		));

		return $this;
	}


    public function getRowUrl($row)
    {
        return $this->getUrl('*/vendor_statements/edit', array('id' => $row->getId()));
    }


}