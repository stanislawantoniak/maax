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
class Licentia_Fidelitas_Block_Adminhtml_Autoresponders_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('campaign_grid');
        $this->setDefaultSort('autoresponder_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('fidelitas/autoresponders')
                ->getResourceCollection();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('autoresponder_id', array(
            'header' => $this->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'autoresponder_id',
        ));

            $this->addColumn('channel', array(
                'header' => $this->__('Channel'),
                'align' => 'left',
                'width' => '80px',
                'index' => 'channel',
                'type' => 'options',
                'options' => array('sms' => $this->__('SMS'), 'email' => $this->__('Email')),
            ));

        $this->addColumn('event', array(
            'header' => $this->__('Event'),
            'align' => 'left',
            'index' => 'event',
            'type' => 'options',
            'options' => Mage::getModel('fidelitas/autoresponders')->toOptionArray(),
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

        $this->addColumn('number_subscribers', array(
            'header' => $this->__('N. Subscribers'),
            'align' => 'right',
            'type' => 'number',
            'index' => 'number_subscribers',
        ));

        $this->addColumn('active', array(
            'header' => $this->__('Is Active?'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'active',
            'type' => 'options',
            'options' => array('0' => $this->__('No'), '1' => $this->__('Yes')),
        ));

        $this->addColumn('from_date', array(
            'header' => $this->__('From Date'),
            'align' => 'left',
            'width' => '120px',
            'type' => 'date',
            'default' => '-NA-',
            'index' => 'from_date',
        ));

        $this->addColumn('to_date', array(
            'header' => $this->__('To Date'),
            'align' => 'left',
            'width' => '120px',
            'type' => 'date',
            'default' => '-NA-',
            'index' => 'to_date',
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
