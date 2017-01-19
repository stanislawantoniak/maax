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
 * Adminhtml transactions grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Zolago_Adminhtml_Block_Sales_Transactions_Grid extends Mage_Adminhtml_Block_Sales_Transactions_Grid
{


    /**
     * Add columns to grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $grid = parent::_prepareColumns();

		$grid->addColumn('bank_transfer_create_at', array(
			'header'    => 'Bank Transfer Date',
			'index'     => 'bank_transfer_create_at',
			'type'      => 'date',
		));

		$grid->addColumn('txn_amount', array(
            'header'    => 'Transaction amount',
            'index'     => 'txn_amount',
            'type'      => 'number',
            'default'   => $this->__('N/A'),
        ));

        $grid->addColumn('txn_status', array(
            'header'    => 'Transaction status',
            'index'     => 'txn_status',
            'type'      => 'options',
            'options'   => array(
                Zolago_Payment_Model_Client::TRANSACTION_STATUS_COMPLETED  => Mage::helper('sales')->__('COMPLETED'),
                Zolago_Payment_Model_Client::TRANSACTION_STATUS_NEW  => Mage::helper('sales')->__('NEW'),
                Zolago_Payment_Model_Client::TRANSACTION_STATUS_PROCESSING  => Mage::helper('sales')->__('PROCESSING'),
                Zolago_Payment_Model_Client::TRANSACTION_STATUS_REJECTED  => Mage::helper('sales')->__('REJECTED'),
            )
        ));

        $grid->addColumn('customer_id', array(
            'header'    => 'Customer ID',
            'index'     => 'customer_id',
            'filter_index' => 'main_table.customer_id',
            'type'      => 'number'
        ));

	    $grid->addColumn('dotpay_id', array(
		    'header'    => 'Dotpay Client ID',
		    'index'     => 'dotpay_id',
		    'type'      => 'number'
	    ));

		$this->addColumn('action',
			array(
				'header'    => Mage::helper('catalog')->__('Action'),
				'width'     => '50px',
				'renderer'  => 'Zolago_Adminhtml_Block_Sales_Transactions_Grid_Renderer_Action',
				'filter'    => false,
				'getter'    => 'getId',
				'sortable'  => false,
				'index'     => 'stores',
			));
        return $grid;
    }

    protected function _useAllocation() {
    	return Mage::helper('zolagopayment')->getConfigUseAllocation();
    }
	protected function _prepareMassaction()
	{
		if (!$this->_useAllocation()) {
			return $this;
		}		
		$this->setMassactionIdField('main_table.entity_id');
		$this->getMassactionBlock()->setFormFieldName('txn');

		$this->getMassactionBlock()->addItem(
			'make_refund',
			array(
				'label' => $this->__('Make refund'),
				'url'   => $this->getUrl('*/payment/massRefund')
			)
		);

		return $this;
	}
	public function getRowUrl($item) {
		if ($this->_useAllocation()) {
			return parent::getRowUrl($item);
		}
		return null;
	}
}
