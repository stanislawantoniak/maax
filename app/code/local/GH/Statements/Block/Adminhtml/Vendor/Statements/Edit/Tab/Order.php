<?php

class GH_Statements_Block_Adminhtml_Vendor_Statements_Edit_Tab_Order
    extends GH_Statements_Block_Adminhtml_Vendor_Statements_Edit_Tab_Statement
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('statement_order');
        $this->setDefaultSort('id');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('ghstatements/order')
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
            'width' => '60',
            'index' => 'id'
        ));
        $this->addColumn('po_increment_id', array(
            'header' => Mage::helper('ghstatements')->__('Order number'),
            'sortable' => true,
            'width' => '60',
            'index' => 'po_increment_id'
        ));
        $this->addColumn('sku', array(
            'header' => Mage::helper('ghstatements')->__('SKU'),
            'sortable' => true,
            'width' => '60',
            'index' => 'sku'
        ));
        $this->addColumn('qty', array(
            'header' => Mage::helper('ghstatements')->__('Qty'),
            'sortable' => true,
            'width' => '60',
            'index' => 'qty',
            "type" => "number"
        ));

        $this->addColumn('shipped_date', array(
            'header' => Mage::helper('ghstatements')->__('Shipping Date'),
            'sortable' => true,
            'width' => '60',
            'index' => 'shipped_date',
            //'type' => 'date',
        ));
        $this->addColumn('carrier', array(
            'header' => Mage::helper('ghstatements')->__('Carrier'),
            'sortable' => true,
            'width' => '60',
            'index' => 'carrier'
        ));

        /** @var $ghDhl GH_Dhl_Model_Source_Shipping */
        $this->addColumn('gallery_shipping_source', array(
            'header' => Mage::helper('ghstatements')->__('Gallery shipping source'),
            'sortable' => true,
            'width' => '60',
            'index' => 'gallery_shipping_source',
            "type" => "options",
            "options" => $ghDhl = Mage::getModel("ghdhl/source_shipping")->toOptionHash()
        ));

        $this->addColumn('payment_method', array(
            'header' => Mage::helper('ghstatements')->__('Payment Method'),
            'sortable' => true,
            'width' => '60',
            'index' => 'payment_method'
        ));

        /** @var $paymentOwner Zolago_Payment_Model_Source_Channel_Owner */
        $this->addColumn('payment_channel_owner', array(
            'header' => Mage::helper('ghstatements')->__('Payment Owner'),
            'sortable' => true,
            'width' => '60',
            'index' => 'payment_channel_owner',
            "type" => "options",
            "options" => $paymentOwner = Mage::getModel("zolagopayment/source_channel_owner")->toOptionHash()
        ));

		$this->addColumn('charge_commission_flag', array(
			'header'	=> Mage::helper('ghstatements')->__('Commission charged'),
			'sortable'	=> true,
			'width'		=> '60',
			'index'		=> 'charge_commission_flag',
			'type'		=> 'options',
			'options'	=> Mage::getSingleton('adminhtml/system_config_source_yesno')->toArray(),
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
        $this->addColumn("shipping_cost", array(
            "index" => "shipping_cost",
            "header" => Mage::helper("ghstatements")->__("Shipping Cost"),
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
        $this->addColumn("value", array(
            "index" => "value",
            "header" => Mage::helper("ghstatements")->__("Order amount"),
            'type' => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base')
        ));
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/orderGrid', array('_current'=>true));
    }

}
