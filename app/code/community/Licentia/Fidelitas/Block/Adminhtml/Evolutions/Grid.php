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
class Licentia_Fidelitas_Block_Adminhtml_Evolutions_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('campaign_grid');
        $this->setDefaultSort('record_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
    }

    protected function _prepareCollection() {

        $collection = Mage::getModel('fidelitas/evolutions')
                ->getResourceCollection();

        if ($segment = Mage::registry('current_segment')) {
            $collection->addFieldToFilter('segment_id',$segment->getId());
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {


        $this->addColumn('listnum', array(
            'header' => $this->__('List Name'),
            'type' => 'options',
            'align' => 'left',
            'options' => Mage::getModel('fidelitas/lists')->getOptionArray(),
            'index' => 'listnum',
        ));

        $this->addColumn('campaign_id', array(
            'header' => $this->__('Campaign'),
            'index' => 'campaign_id',
            'type' => 'options',
            'options' => Mage::getModel('fidelitas/campaigns')->toFormValues(),
        ));

        if (!Mage::registry('current_segment')) {
            $this->addColumn('segment_id', array(
                'header' => $this->__('Segment Name'),
                'align' => 'left',
                'index' => 'segment_id',
                'type' => 'options',
                'options' => Mage::getModel('fidelitas/segments')->toFormValues(),
            ));
        }

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


        $this->addColumn('cellphone', array(
            'header' => $this->__('Cellphone'),
            'align' => 'left',
            'index' => 'cellphone',
        ));

        $this->addColumn('conversions_number', array(
            'header' => $this->__('Conversions'),
            'align' => 'left',
            'width' => '80px',
            'type' => 'number',
            'index' => 'conversions_number',
        ));

        $this->addColumn('conversions_amount', array(
            'header' => $this->__('Conv. Amount'),
            'align' => 'left',
            'width' => '80px',
            'type' => 'currency',
            'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
            'index' => 'conversions_amount',
        ));

        $this->addColumn('conversions_average', array(
            'header' => $this->__('Conv. AVG'),
            'align' => 'left',
            'width' => '80px',
            'type' => 'currency',
            'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
            'index' => 'conversions_average',
        ));

        $this->addColumn('created_at', array(
            'header' => $this->__('Created at'),
            'align' => 'left',
            'type' => 'date',
            'index' => 'created_at',
        ));

        $this->addColumn('action', array(
            'header' => $this->__('Action'),
            'type' => 'action',
            'align' => 'center',
            'width' => '150px',
            'filter' => false,
            'sortable' => false,
            'actions' => array(array(
                    'url' => $this->getUrl('*/customer/edit', array('id' => '$customer_id')),
                    'caption' => Mage::helper('adminhtml')->__('View Customer'),
                )),
            'index' => 'type',
            'is_system' => true,
            'sortable' => false
        ));

        $this->addExportType('*/*/exportCsv', $this->__('CSV'));
        $this->addExportType('*/*/exportXml', $this->__('Excel XML'));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row) {
        return false;
    }

    public function customerResult($value) {

        if ((int) $value > 0) {
            $url = $this->getUrl('/customer/edit', array('id' => $value));
            return'<a href="' . $url . '">' . $this->__('View') . '</a>';
        }

        return $this->__('No');
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    public function subscriberResult($value) {

        if ((int) $value > 0) {
            $url = $this->getUrl('/fidelias_subscriber/edit', array('id' => $value));
            return'<a href="' . $url . '">' . $this->__('View') . '</a>';
        }

        return $this->__('No');
    }

}
