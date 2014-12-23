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
class Licentia_Fidelitas_Block_Adminhtml_Followup_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('campaign_grid');
        $this->setDefaultSort('followup_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('fidelitas/followup')
                ->getResourceCollection();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('followup_id', array(
            'header' => $this->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'followup_id',
        ));

        $this->addColumn('channel', array(
            'header' => $this->__('Channel'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'channel',
            'type' => 'options',
            'options' => array('sms' => $this->__('SMS'), 'email' => $this->__('Email')),
        ));

        $this->addColumn('campaign_id', array(
            'header' => $this->__('Campaign'),
            'align' => 'left',
            'index' => 'campaign_id',
            'type' => 'options',
            'options' => Mage::getModel('fidelitas/campaigns')->toFormValues(),
        ));

        $this->addColumn('segment_id', array(
            'header' => $this->__('Segment'),
            'align' => 'left',
            'default' => '-NA-',
            'index' => 'segment_id',
            'type' => 'options',
            'options' => Mage::getModel('fidelitas/segments')->toFormValues(),
        ));

        $this->addColumn('name', array(
            'header' => $this->__('Name'),
            'align' => 'left',
            'index' => 'name',
        ));

        $this->addColumn('send_at', array(
            'header' => $this->__('Send At'),
            'align' => 'left',
            'type' => 'datetime',
            'index' => 'send_at',
        ));

        $this->addColumn('sent', array(
            'header' => $this->__('Sent?'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'sent',
            'type' => 'options',
            'options' => array('0' => $this->__('No'), '1' => $this->__('Yes')),
        ));

        $this->addColumn('active', array(
            'header' => $this->__('Is Active?'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'active',
            'type' => 'options',
            'options' => array('0' => $this->__('No'), '1' => $this->__('Yes')),
        ));

        $this->addColumn('action', array(
            'header' => $this->__('Campaign'),
            'type' => 'action',
            'align' => 'center',
            'system' => true,
            'filter' => false,
            'sortable' => false,
            'actions' => array(array(
                    'url' => $this->getUrl('adminhtml/fidelitas_campaigns/edit', array('id' => '$campaign_id')),
                    'caption' => $this->__('View'),
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
