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
class Licentia_Fidelitas_Block_Adminhtml_Subscribers_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('campaign_grid');
        $this->setDefaultSort('subscriber_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection() {

        $list = Mage::registry('current_list');

        $collection = Mage::getModel('fidelitas/subscribers')
                ->getResourceCollection();

        $listsTable = Mage::getSingleton('core/resource')->getTableName('fidelitas/lists');

        $collection->getSelect()
                ->joinLeft($listsTable, $listsTable . '.listnum = main_table.list ', array('*'));

        if ($list->getId() > 0) {
            $collection->addFieldToFilter('list', $list->getListnum());
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('uid', array(
            'header' => $this->__('ID'),
            'align' => 'right',
            'width' => '90px',
            'index' => 'uid',
        ));
        $this->addColumn('customer_id', array(
            'header' => $this->__('Customer'),
            'align' => 'center',
            'width' => '50px',
            'index' => 'customer_id',
            'frame_callback' => array($this, 'customerResult'),
        ));

        $this->addColumn('title', array(
            'header' => $this->__('List Name'),
            'type' => 'options',
            'width' => '150px',
            'align' => 'left',
            'options' => Mage::getModel('fidelitas/lists')->getOptionArray(),
            'index' => 'listnum',
        ));
        /*
          $this->addColumn('store_id', array(
          'header' => $this->__('List Store View'),
          'type' => 'store',
          'width' => '150px',
          'index' => 'store_id',
          ));
         */
        $this->addColumn('first_name', array(
            'header' => $this->__('First Name'),
            'align' => 'left',
            'index' => 'first_name',
        ));

        $this->addColumn('last_name', array(
            'header' => $this->__('Last Name'),
            'align' => 'left',
            'index' => 'last_name',
        ));

        $this->addColumn('email', array(
            'header' => $this->__('Email'),
            'align' => 'left',
            'index' => 'email',
        ));

        $this->addColumn('email_sent', array(
            'header' => $this->__('Emails Sent'),
            'align' => 'left',
            'index' => 'email_sent',
            'type' => 'number',
            'width' => '40px',
        ));

        $this->addColumn('email_views', array(
            'header' => $this->__('Email Views'),
            'align' => 'left',
            'index' => 'email_views',
            'type' => 'number',
            'width' => '50px',
        ));

        $this->addColumn('sms_delivered', array(
            'header' => $this->__('SMS Delivered'),
            'align' => 'left',
            'index' => 'sms_delivered',
            'type' => 'number',
            'width' => '50px',
        ));
        /*
          $this->addColumn('conversions_number', array(
          'header' => $this->__('Conversions'),
          'align' => 'left',
          'width' => '80px',
          'type' => 'number',
          'index' => 'conversions_number',
          ));
         */
        $this->addColumn('conversions_amount', array(
            'header' => $this->__('Conv. Amount'),
            'align' => 'left',
            'width' => '80px',
            'type' => 'currency',
            'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
            'index' => 'conversions_amount',
        ));
        /*
          $this->addColumn('conversions_average', array(
          'header' => $this->__('Conv. AVG'),
          'align' => 'left',
          'width' => '80px',
          'type' => 'currency',
          'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
          'index' => 'conversions_average',
          ));
         */
        $this->addColumn('conv', array(
            'header' => $this->__('Detail'),
            'type' => 'action',
            'width' => '50px',
            'filter' => false,
            'sortable' => false,
            'actions' => array(array(
                    'url' => $this->getUrl('adminhtml/fidelitas_subscribers/conversions', array('id' => '$subscriber_id')),
                    'caption' => $this->__('Conversions'),
                )),
            'index' => 'type',
            'sortable' => false
        ));

        return parent::_prepareColumns();
    }

    protected function _filterStoreCondition($collection, $column) {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }

        $this->getCollection()->addStoreFilter($value);
    }

    public function customerResult($value) {

        if ((int) $value > 0) {
            $url = $this->getUrl('adminhtml/customer/edit', array('id' => $value));
            return'<a href="' . $url . '">' . $this->__('Yes') . '</a>';
        }

        return $this->__('No');
    }

    public function getGridUrl() {
        $list = Mage::registry('current_list');
        return $this->getUrl('*/*/grid', array('_current' => true, 'list' => $list->getId()));
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}
