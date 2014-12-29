<?php

/**
 * Licentia Fidelitas - Advanced Email and SMS Marketing Automation for E-Goi
 *
 * NOTICE OF LICENSE
 * This source file is subject to the Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International  
 * It is available through the world-wide-web at this URL:
 * http://creativecommons.org/licenses/by-nc-nd/4.0/
 *
 * @title      Advanced Email and SMS Marketing Automation
 * @category   Marketing
 * @package    Licentia
 * @author     Bento Vilas Boas <bento@licentia.pt>
 * @copyright  Copyright (c) 2012 Licentia - http://licentia.pt
 * @license    Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International 
 */
class Licentia_Fidelitas_Block_Adminhtml_Coupons_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('coupons_grid');
        $this->setDefaultSort('coupon_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('fidelitas/coupons')
                ->getResourceCollection();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {

        $this->addColumn('coupon_id', array(
            'header' => $this->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'coupon_id',
        ));

        $this->addColumn('campaign_id', array(
            'header' => $this->__('Campaign'),
            'index' => 'campaign_id',
            'type' => 'options',
            'options' => Mage::getModel('fidelitas/campaigns')->toFormValues(),
        ));

        $this->addColumn('rule_id', array(
            'header' => $this->__('Promotion Rule'),
            'type' => 'options',
            'index' => 'rule_id',
            'options' => Mage::getModel('fidelitas/coupons')->toFormValues(),
        ));

        $this->addColumn('coupon_code', array(
            'header' => $this->__('Coupon Code'),
            'index' => 'coupon_code',
        ));

        $this->addColumn('times_used', array(
            'header' => $this->__('Used'),
            'index' => 'times_used',
            'type' => 'options',
            'options' => array(
                '0' => $this->__('No'),
                '1' => $this->__('Yes'),
            ),
        ));

        $this->addColumn('subscriber_email', array(
            'header' => $this->__('Subscriber Email'),
            'index' => 'subscriber_email',
        ));

        $this->addColumn('customer_id', array(
            'header' => $this->__('Customer'),
            'align' => 'center',
            'width' => '50px',
            'index' => 'customer_id',
            'frame_callback' => array($this, 'customerResult'),
            'is_system' => true,
        ));

        $this->addColumn('created_at', array(
            'header' => $this->__('Created at'),
            'align' => 'left',
            'type' => 'datetime',
            'index' => 'created_at',
        ));

        $this->addColumn('used_at', array(
            'header' => $this->__('Used at'),
            'align' => 'left',
            'type' => 'datetime',
            'index' => 'used_at',
        ));

        $this->addColumn('order_id', array(
            'header' => $this->__('Order'),
            'align' => 'left',
            'width' => '100px',
            'index' => 'order_id',
            'frame_callback' => array($this, 'orderResult'),
            'is_system' => true,
        ));

        $this->addExportType('*/*/exportCsv', $this->__('CSV'));
        $this->addExportType('*/*/exportXml', $this->__('Excel XML'));

        return parent::_prepareColumns();
    }

    public function orderResult($value) {

        if ((int) $value > 0) {
            $url = $this->getUrl('/sales_order/view', array('order_id' => $value));
            return'<a href="' . $url . '">[ID:' . $value . '] ' . $this->__('View') . '</a>';
        }

        return $this->__('N/A');
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    public function customerResult($value) {

        if ((int) $value > 0) {
            $url = $this->getUrl('/customer/edit', array('id' => $value));
            return'<a href="' . $url . '">' . $this->__('Yes') . '</a>';
        }

        return $this->__('No');
    }

}
