<?php

/**
 * Grid for customer coupons
 *
 * Class Zolago_Adminhtml_Block_Customer_Coupon_Grid
 */
class Zolago_Adminhtml_Block_Customer_Coupon_Grid extends Mage_Adminhtml_Block_Widget_Grid {

	public function __construct() {
		parent::__construct();
		$this->setId('Customer_Coupon_Grid');
		$this->setDefaultSort('expiration_date');
	}

	/**
	 * Get coupon collection
	 *
	 * @return Zolago_SalesRule_Model_Resource_Coupon_Collection
	 */
	public function getCollectionObject() {
		return Mage::getResourceModel('zolagosalesrule/coupon_collection');
	}

	protected function _prepareCollection() {
		$collection = $this->getCollectionObject();
		$collection->addCustomerIdFilter($this->getCustomerId());
		$collection->addCustomerTimesUsedInfo();

		// Add info from Rule table
		$collection->getSelect()->joinLeft(
			array('tRule' => $collection->getTable('salesrule/rule')),
			"main_table.rule_id = tRule.rule_id",
			array(
				'rule_name' => 'tRule.name',
				'rule_payer' => 'tRule.rule_payer',
				'rule_is_active' => 'tRule.is_active'
			)
		);
		// Add campaign name (fronted name)
		$collection->getSelect()->joinLeft(
			array('tCampaign' => $collection->getTable('zolagocampaign/campaign')),
			"tRule.campaign_id = tCampaign.campaign_id",
			array('campaign_name' => 'tCampaign.name_customer')
		);


		$this->setCollection($collection);

		parent::_prepareCollection();
		return $this;
	}

	protected function _prepareColumns() {
		/** @var GH_Beacon_Helper_Data $helper */
		$helper = Mage::helper('zolagosalesrule');

		$this->addColumn('rule_name', array(
			'header' => $helper->__('Rule name'),
			'index' => 'rule_name',
			'filter_index' => 'tRule.name',
		));

		$this->addColumn('type', array(
			'header' => $helper->__('Promotion type'),
			'index' => 'type',
			'type' => 'options',
			'options' => Mage::getSingleton('zolagosalesrule/promotion_type')->toOptionHash(),
			'filter_index' => 'main_table.type',
		));

		$this->addColumn('campaign_name', array(
			'header' => $helper->__('Campaign name'),
			'index' => 'campaign_name',
			'filter_index' => 'tCampaign.name_customer',
		));

		$this->addColumn('rule_payer', array(
			'header' => $helper->__('Rule payer'),
			'index' => 'rule_payer',
			'type' => 'options',
			'options' => Mage::getSingleton('zolagosalesrule/rule_payer')->toOptionHash(),
			'filter_index' => 'tRule.rule_payer',
		));

		$this->addColumn('code', array(
			'header' => $helper->__('Coupon code'),
			'index' => 'code',
			'filter_index' => 'main_table.code',
		));

		$this->addColumn('expiration_date', array(
			'header' => $helper->__('Expiration date'),
			'index' => 'expiration_date',
			'type' => 'date',
			'filter_index' => 'main_table.expiration_date',
		));

		$valueExpr = $this->getCollectionObject()->getSelect()->getAdapter()
			->getCheckSql("tCouponUsage.times_used > 0", "tCouponUsage.times_used", "0");
		$this->addColumn('customer_times_used', array(
			'header' => $helper->__('Times used'),
			'index' => 'customer_times_used',
			'type' => 'number',
			'filter_index' => $valueExpr
		));

		$this->addColumn('rule_is_active', array(
			'header' => $helper->__('Rule is active'),
			'index' => 'rule_is_active',
			'type' => 'options',
			'options' => Mage::getSingleton('adminhtml/system_config_source_yesno')->toArray(),
			'filter_index' => 'tRule.is_active'
		));

		return parent::_prepareColumns();
	}

	/**
	 * Retrieve customer ID
	 *
	 * @return int
	 * @throws Exception
	 */
	protected function getCustomerId() {
		return (int)$this->getRequest()->getParam('id');
	}
}
