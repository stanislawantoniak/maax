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
class Licentia_Fidelitas_Block_Adminhtml_Events_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('events_grid');
        $this->setDefaultSort('event_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('fidelitas/events')
                ->getResourceCollection()
                ->addFieldToFilter('sent', 0);

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {

        $this->addColumn('event_id', array(
            'header' => $this->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'event_id',
        ));
        $this->addColumn('event', array(
            'header' => $this->__('Event'),
            'index' => 'event',
            'type' => 'options',
            'options' => Mage::getModel('fidelitas/autoresponders')->toOptionArray(),
        ));

        $this->addColumn('autoresponder_id', array(
            'header' => $this->__('Autoresponder'),
            'index' => 'autoresponder_id',
            'type' => 'options',
            'options' => Mage::getModel('fidelitas/autoresponders')->toFormValues(),
        ));

        $this->addColumn('customer_id', array(
            'header' => $this->__('Customer'),
            'align' => 'center',
            'width' => '50px',
            'index' => 'customer_id',
            'frame_callback' => array($this, 'customerResult'),
            'is_system' => true,
        ));

        $this->addColumn('subscriber_firstname', array(
            'header' => $this->__('Name'),
            'index' => array('subscriber_firstname', 'subscriber_lastname'),
            'type' => 'text',
            'renderer' => 'Licentia_Fidelitas_Block_Adminhtml_Widget_Grid_Column_Renderer_Concat',
            'separator' => ' ',
            'filter_index' => "CONCAT(subscriber_firstname, ' ',subscriber_firstname)",
        ));

        $this->addColumn('subscriber_email', array(
            'header' => $this->__('Email'),
            'index' => 'subscriber_email',
        ));

        $this->addColumn('subscriber_cellphone', array(
            'header' => $this->__('Cellphone'),
            'index' => 'subscriber_cellphone',
        ));

        $this->addColumn('channel', array(
            'header' => $this->__('Channel'),
            'index' => 'channel',
            'type' => 'options',
            'options' => array(
                'email' => $this->__('Email'),
                'sms' => $this->__('SMS'),
            ),
        ));

        $this->addColumn('created_at', array(
            'header' => $this->__('Created at'),
            'align' => 'left',
            'width' => '170px',
            'type' => 'datetime',
            'index' => 'created_at',
        ));

        $this->addColumn('send_at', array(
            'header' => $this->__('Send at'),
            'align' => 'left',
            'width' => '170px',
            'type' => 'datetime',
            'index' => 'send_at',
        ));

        $this->addExportType('*/*/exportCsv', $this->__('CSV'));
        $this->addExportType('*/*/exportXml', $this->__('Excel XML'));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction() {
        $this->setMassactionIdField('event_id');
        $this->getMassactionBlock()->setFormFieldName('events');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => $this->__('Delete'),
            'url' => $this->getUrl('*/fidelitas_events/massDelete'),
            'confirm' => Mage::helper('customer')->__('Are you sure?')
        ));

        return $this;
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
