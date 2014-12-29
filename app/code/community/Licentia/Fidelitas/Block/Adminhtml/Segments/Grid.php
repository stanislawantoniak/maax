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
class Licentia_Fidelitas_Block_Adminhtml_Segments_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('importerGrid');
        $this->setDefaultSort('segment_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('fidelitas/segments')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('segment_id', array(
            'header' => $this->__('ID'),
            'width' => '50px',
            'index' => 'segment_id',
        ));

        $this->addColumn('name', array(
            'header' => $this->__('Name'),
            'align' => 'left',
            'index' => 'name',
        ));

        $this->addColumn('records', array(
            'header' => $this->__('Records (est. All lists)'),
            'type' => 'number',
            'width' => '120px',
            'index' => 'records',
        ));

        $this->addColumn('is_active', array(
            'header' => $this->__('Active'),
            'align' => 'right',
            'width' => '120px',
            'index' => 'is_active',
            'type' => 'options',
            'options' => array(
                1 => $this->__('Yes'),
                0 => $this->__('No'),
            ),
        ));
        $this->addColumn('seg_type', array(
            'header' => $this->__('Type'),
            'align' => 'right',
            'width' => '180px',
            'index' => 'type',
            'type' => 'options',
            'options' => array(
                'customers' => $this->__('Registered Customers'),
                'visitors' => $this->__('Guest Users'),
                'both' => $this->__('Registered Customers and Guest Users')
            ),
        ));

        $this->addColumn('cron', array(
            'header' => $this->__('Update Subs.'),
            'align' => 'right',
            'width' => '80px',
            'index' => 'cron',
            'type' => 'options',
            'options' => array(
                '0' => $this->__('No'),
                'd' => $this->__('Daily'),
                'w' => $this->__('Weekly'),
                'm' => $this->__('Monthly')),
        ));

        $this->addColumn('last_update', array(
            'header' => $this->__('Last Update'),
            'type' => 'datetime',
            'width' => '170px',
            'index' => 'last_update',
        ));

        $this->addColumn('action', array(
            'header' => $this->__('Detail'),
            'type' => 'action',
            'align' => 'center',
            'width' => '80px',
            'filter' => false,
            'sortable' => false,
            'actions' => array(array(
                    'url' => $this->getUrl('*/*/records', array('id' => '$segment_id')),
                    'caption' => Mage::helper('adminhtml')->__('Records'),
                )),
            'index' => 'type',
            'sortable' => false
        ));

        return parent::_prepareColumns();
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}
