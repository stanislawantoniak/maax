<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml transaction details grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Zolago_Adminhtml_Block_Sales_Transactions_Allocations_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	/**
	 * Initialize default sorting and html ID
	 */
	protected function _construct()
	{
		$this->setId('transactionAllocationsGrid');
		$this->setPagerVisibility(false);
		$this->setFilterVisibility(false);
	}

	/**
	 * Prepare collection for grid
	 *
	 * @return Mage_Adminhtml_Block_Widget_Grid
	 */
	protected function _prepareCollection()
	{
		$this->setCollection($this->getAllocations());

		return parent::_prepareCollection();
	}

	/**
	 * Add columns to grid
	 *
	 * @return Mage_Adminhtml_Block_Widget_Grid
	 */
	protected function _prepareColumns()
	{
		$_helper = Mage::helper("zolagopayment");
		$_adminHelper = Mage::helper("adminhtml");

		$this->addColumn('allocation_id', array(
			'header'    => $_helper->__('ID'),
			'index'     => 'allocation_id',
			'sortable'  => false,
			'type'      => 'text',
		));

		$this->addColumn('po_increment_id', array(
			'header'    => $_helper->__('PO ID'),
			'index'     => 'increment_id',
			'sortable'  => false,
			'type'      => 'text',
			'escape'    => true
		));

		$this->addColumn('allocation_amount', array(
			'header'    => $_helper->__('Amount'),
			'index'     => 'allocation_amount',
			'sortable'  => false,
			'type'      => 'currency',
			'currency_code'  => Mage::registry('current_transaction')->getOrder()->getOrderCurrency()->getCurrencyCode(),
		));

		$this->addColumn('allocation_type', array(
			'header'    => $_helper->__('Type'),
			'index'     => 'allocation_type',
			'sortable'  => false,
			'type'      => 'text',
			'escape'    => true
		));

		$this->addColumn('operator_email', array(
			'header'    => $_helper->__('Operator'),
			'index'     => 'operator_email',
			'sortable'  => false,
			'type'      => 'text',
			'escape'    => true
		));

		$this->addColumn('created_at', array(
			'header'    => $_helper->__('Date'),
			'index'     => 'created_at',
			'sortable'  => false,
			'type'      => 'text',
			'escape'    => true
		));

		$this->addColumn('comment', array(
			'header'    => $_helper->__('Comment'),
			'index'     => 'comment',
			'sortable'  => false,
			'type'      => 'text',
			'escape'    => true
		));

		$this->addColumn('customer', array(
			'header'    => $_helper->__('Customer'),
			'index'     => 'customer_email',
			'sortable'  => false,
			'type'      => 'text',
			'escape'    => true
		));

		$this->addColumn('vendor', array(
			'header'    => $_helper->__('Vendor'),
			'index'     => 'vendor_name',
			'sortable'  => false,
			'type'      => 'text',
			'escape'    => true
		));

		$this->addColumn('auto', array(
			'header'    => $_helper->__('Automat'),
			'index'     => 'is_automat',
			'sortable'  => false,
			'type'      => 'options',
			'options' => array('1' => $_adminHelper->__('Yes'), '0' => $_adminHelper->__('No'))
		));

		$this->addColumn('refund_transaction_id', array(
			'header'    => $_helper->__('Refund transaction'),
			'index'     => 'refund_transaction_txn_id',
			'sortable'  => false,
			'type'      => 'text',
			'escape'    => true
		));

		$this->addColumn('rma_increment_id', array(
			'header'    => $_helper->__('RMA ID'),
			'index'     => 'rma_increment_id',
			'sortable'  => false,
			'type'      => 'text',
			'escape'    => true
		));

		$this->addColumn('primary', array(
			'header'    => $_helper->__('Primary'),
			'index'     => 'primary',
			'sortable'  => false,
			'type'      => 'options',
			'options' => array('1' => $_adminHelper->__('Yes'), '0' => $_adminHelper->__('No'))
		));

		return parent::_prepareColumns();
	}

	/**
	 * @return Zolago_Payment_Model_Resource_Allocation_Collection
	 */
	public function getAllocations() {
		/** @var Zolago_Payment_Model_Allocation $allocationModel */
		$allocationModel = Mage::getModel('zolagopayment/allocation');

		/** @var Mage_Sales_Model_Order_Payment_Transaction $transaction */
		$transaction = Mage::registry('current_transaction');

		return $allocationModel->getTransactionAllocations($transaction);
	}
}
