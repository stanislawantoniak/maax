<?php
class Zolago_Adminhtml_Block_Promo_Quote_Edit_Tab_Coupons_Grid extends Mage_Adminhtml_Block_Promo_Quote_Edit_Tab_Coupons_Grid
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Define grid columns
	 *
	 * @return Mage_Adminhtml_Block_Widget_Grid
	 */
	protected function _prepareColumns()
	{
		$this->addColumn('code', array(
			'header' => Mage::helper('salesrule')->__('Coupon Code'),
			'index' => 'code'
		));

		$this->addColumn('created_at', array(
			'header' => Mage::helper('salesrule')->__('Created On'),
			'index' => 'created_at',
			'type' => 'datetime',
			'align' => 'center',
			'width' => '160'
		));

		$this->addColumn('used', array(
			'header' => Mage::helper('salesrule')->__('Used'),
			'index' => 'times_used',
			'width' => '100',
			'type' => 'options',
			'options' => array(
				Mage::helper('adminhtml')->__('No'),
				Mage::helper('adminhtml')->__('Yes')
			),
			'renderer' => 'adminhtml/promo_quote_edit_tab_coupons_grid_column_renderer_used',
			'filter_condition_callback' => array(
				Mage::getResourceModel('salesrule/coupon_collection'), 'addIsUsedFilterCallback'
			)
		));

		$this->addColumn('times_used', array(
			'header' => Mage::helper('salesrule')->__('Times Used'),
			'index' => 'times_used',
			'width' => '50',
			'type' => 'number',
		));

		$this->addColumn('newsletter_sent', array(
			'header' => Mage::helper('salesrule')->__('Sent in newsletter'),
			'index' => 'newsletter_sent',
			'width' => '50',
			'type' => 'number',
			'type' => 'options',
			'options' => array(
				Mage::helper('adminhtml')->__('No'),
				Mage::helper('adminhtml')->__('Yes')
			),
			'renderer' => 'adminhtml/promo_quote_edit_tab_coupons_grid_column_renderer_used',
		));

		$this->addExportType('*/*/exportCouponsCsv', Mage::helper('customer')->__('CSV'));
		$this->addExportType('*/*/exportCouponsXml', Mage::helper('customer')->__('Excel XML'));
		return parent::_prepareColumns();
	}
}
