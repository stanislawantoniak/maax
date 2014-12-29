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
class Licentia_Fidelitas_Block_Adminhtml_Campaigns_Children_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('campaign_grid');
        $this->setDefaultSort('campaign_id');
        $this->setDefaultDir('DESC');
        $this->setEmptyText($this->__('No campaigns have been sent yet.'));
        $this->setFilterVisibility(false);
        $this->setSortable(false);
        $this->setPagerVisibility(false);
    }

    protected function _prepareCollection() {

        $current = Mage::registry('current_campaign');

        $collection = Mage::getModel('fidelitas/campaigns')
                ->getResourceCollection()
                ->addFieldToFilter('parent_id', $current->getId());

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('ref', array(
            'header' => $this->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'ref',
        ));

        $this->addColumn('channel', array(
            'header' => $this->__('Channel'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'channel',
            'type' => 'options',
            'options' => array(
                'Email' => 'Email',
                'SMS' => 'SMS',
            ),
        ));

        $this->addColumn('internal_name', array(
            'header' => $this->__('Campaign Name'),
            'align' => 'left',
            'index' => 'internal_name',
        ));

        $this->addColumn('subject', array(
            'header' => $this->__('Subject'),
            'align' => 'left',
            'index' => 'subject',
        ));

        $this->addColumn('local_status', array(
            'header' => $this->__('Status'),
            'align' => 'left',
            'width' => '80px',
            'frame_callback' => array($this, 'serviceResult'),
            'index' => 'local_status',
        ));


        $this->addColumn('recurring_last_run', array(
            'header' => $this->__('Run'),
            'align' => 'left',
            'width' => '150px',
            'default' => $this->__('-Not deployed yet-'),
            'index' => 'start',
            'type' => 'datetime',
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

        $this->addColumn('conv', array(
            'header' => $this->__('Detail'),
            'type' => 'action',
            'width' => '50px',
            'filter' => false,
            'sortable' => false,
            'actions' => array(array(
                    'url' => $this->getUrl('adminhtml/fidelitas_campaigns/conversions', array('id' => '$campaign_id')),
                    'caption' => $this->__('Conversions'),
                )),
            'index' => 'type',
            'sortable' => false
        ));

        $this->addColumn('action', array(
            'header' => $this->__('View'),
            'type' => 'action',
            'width' => '50px',
            'filter' => false,
            'sortable' => false,
            'actions' => array(array(
                    'url' => $this->getUrl('adminhtml/fidelitas_reports/detail', array('id' => '$campaign_id')),
                    'caption' => $this->__('Reports'),
                )),
            'index' => 'type',
            'sortable' => false
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row) {
        return false;
    }

    public function serviceResult($value, $row) {

        if ($value == "standby")
            return' <span class="grid-severity-minor"><span>' . $this->__('Stand By') . '</span></span>';

        if ($value == "running")
            return' <span class="grid-severity-major"><span>' . $this->__('Running') . '</span></span>';

        if ($value == "finished")
            return' <span class="grid-severity-notice"><span>' . $this->__('Finished') . '</span></span>';

        if ($value == "error")
            return' <span class="grid-severity-critical"><span>' . $this->__('Error') . ' (' . $row->getServiceResponse() . ')</span></span>';
    }

}
