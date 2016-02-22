<?php

class GH_Statements_Block_Adminhtml_Vendor_Statements_Edit_Tab_Rma
    extends GH_Statements_Block_Adminhtml_Vendor_Statements_Edit_Tab_Statement
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('statement_rma');
        $this->setDefaultSort('id');
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('ghstatements/rma')
            ->getCollection()
            ->addFieldToFilter('statement_id', $this->getStatement()->getId());

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header' => Mage::helper('ghstatements')->__('ID'),
            'sortable' => true,
            'index' => 'id'
        ));
        $this->addColumn('po_increment_id', array(
            'header' => Mage::helper('ghstatements')->__('Order number'),
            'sortable' => true,
            'index' => 'po_increment_id'
        ));
        $this->addColumn('rma_increment_id', array(
            'header' => Mage::helper('ghstatements')->__('RMA number'),
            'sortable' => true,
            'index' => 'rma_increment_id'
        ));
        $this->addColumn('event_date', array(
            'header' => Mage::helper('ghstatements')->__('RMA Date'),
            'sortable' => true,
            'index' => 'event_date',
            'type' => 'date',
        ));
        $this->addColumn('sku', array(
            'header' => Mage::helper('ghstatements')->__('SKU'),
            'sortable' => true,
            'index' => 'sku'
        ));
        $this->addColumn('reason', array(
            'header' => Mage::helper('ghstatements')->__('Reason'),
            'sortable' => true,
            'index' => 'reason'
        ));
        $this->addColumn('payment_method', array(
            'header' => Mage::helper('ghstatements')->__('Payment Method'),
            'sortable' => true,
            'index' => 'payment_method'
        ));
        $this->addColumn('payment_channel_owner', array(
            'header' => Mage::helper('ghstatements')->__('Payment Owner'),
            'sortable' => true,
            'index' => 'payment_channel_owner',
            "type" => "options",
            "options" => Mage::getModel("zolagopayment/source_channel_owner")->toOptionHash()
        ));
		$this->addColumn('charge_commission_flag', array(
			'header'	=> Mage::helper('ghstatements')->__('Commission charged'),
			'sortable'	=> true,
			'width'		=> '60',
			'index'		=> 'charge_commission_flag',
			'type'		=> 'options',
			'options'	=> Mage::getSingleton('adminhtml/system_config_source_yesno')->toArray(),
		));
        $this->addColumn('approved_refund_amount', array(
            'header' => Mage::helper('ghstatements')->__('Approved refund amount'),
            'index' => 'approved_refund_amount',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
        ));
        $this->addColumn("price", array(
            "index" => "price",
            "header" => Mage::helper("ghstatements")->__("Price"),
            'type' => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base')
        ));
        $this->addColumn("discount_amount", array(
            "index" => "discount_amount",
            "header" => Mage::helper("ghstatements")->__("Discount Amount"),
            'type' => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base')
        ));
        $this->addColumn("final_price", array(
            "index" => "final_price",
            "header" => Mage::helper("ghstatements")->__("Final Price"),
            'type' => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base')
        ));
        $this->addColumn("commission_percent", array(
            "index" => "commission_percent",
            "header" => Mage::helper("ghstatements")->__("Commission Percent"),
            'type' => 'number',
            'renderer'  => 'GH_Statements_Block_Adminhtml_Calendar_Grid_Column_Renderer_Percent',
        ));
        $this->addColumn("gallery_discount_value", array(
            "index" => "gallery_discount_value",
            "header" => Mage::helper("ghstatements")->__("Gallery Discount"),
            'type' => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base')
        ));
        $this->addColumn("commission_value", array(
            "index" => "commission_value",
            "header" => Mage::helper("ghstatements")->__("Commission"),
            'type' => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base')
        ));
        $this->addColumn("commission_return", array(
            "index" => "commission_return",
            "header" => Mage::helper("ghstatements")->__("Commission return"),
            'type' => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base')
        ));
        $this->addColumn("discount_return", array(
            "index" => "discount_return",
            "header" => Mage::helper("ghstatements")->__("Discount return"),
            'type' => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base')
        ));
        $this->addColumn("value", array(
            "index" => "value",
            "header" => Mage::helper("ghstatements")->__("To return"),
            'type' => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base')
        ));
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/rmaGrid', array('_current'=>true));
    }

}
