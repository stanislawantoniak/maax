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
class Licentia_Fidelitas_Block_Adminhtml_Campaigns_Edit_Tab_Followup extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('campaign_grid');
        $this->setDefaultSort('followup_id');
        $this->setDefaultDir('DESC');
        $this->setFilterVisibility(false);
        $this->setSortable(false);
        $this->setPagerVisibility(false);
    }

    protected function _prepareCollection() {

        $current = Mage::registry('current_campaign');

        $collection = Mage::getModel('fidelitas/followup')
                ->getResourceCollection()
                ->addFieldToFilter('campaign_id', $current->getId());

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

        $this->addColumn('active', array(
            'header' => $this->__('Is Active?'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'active',
            'type' => 'options',
            'options' => array('0' => $this->__('No'), '1' => $this->__('Yes')),
        ));


        return parent::_prepareColumns();
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/fidelitas_followup/edit', array('id' => $row->getId()));
    }

}
