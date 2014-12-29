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
class Licentia_Fidelitas_Block_Adminhtml_Events_Campaigns_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('campaign_grid');
        $this->setDefaultSort('campaign_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('fidelitas/campaigns')
                ->getResourceCollection()
                ->addFieldToFilter('autoresponder', array('gt' => 0));

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('campaign_id', array(
            'header' => $this->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'campaign_id',
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

        $this->addColumn('autoresponder_event', array(
            'header' => $this->__('Event Type'),
            'index' => 'autoresponder_event',
            'type' => 'options',
            'options' => Mage::getModel('fidelitas/autoresponders')->toOptionArray(),
        ));

        $this->addColumn('internal_name', array(
            'header' => $this->__('Name'),
            'align' => 'left',
            'index' => 'internal_name',
        ));
        /*
          $this->addColumn('subject', array(
          'header' => $this->__('Subject'),
          'align' => 'left',
          'index' => 'subject',
          ));
         */
        $this->addColumn('autoresponder_recipient', array(
            'header' => $this->__('Recipient'),
            'align' => 'left',
            'index' => 'autoresponder_recipient',
        ));

        $this->addColumn('local_status', array(
            'header' => $this->__('Status'),
            'align' => 'left',
            'width' => '80px',
            'frame_callback' => array($this, 'serviceResult'),
            'index' => 'local_status',
        ));

        $this->addColumn('recurring_next_run', array(
            'header' => $this->__('Deploy Date'),
            'align' => 'left',
            'width' => '180px',
            'type' => 'datetime',
            'default' => '-NA-',
            'index' => 'recurring_next_run',
        ));

        $this->addColumn('conversions_amount', array(
            'header' => $this->__('Conv. Amount'),
            'align' => 'left',
            'width' => '80px',
            'default' => '-NA-',
            'type' => 'currency',
            'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
            'index' => 'conversions_amount',
        ));

        $this->addColumn('views', array(
            'header' => $this->__('Views'),
            'align' => 'right',
            'default' => '-NA-',
            'index' => 'views',
            'width' => '20px',
        ));

        $this->addColumn('clicks', array(
            'header' => $this->__('Clicks'),
            'align' => 'right',
            'default' => '-NA-',
            'index' => 'clicks',
            'width' => '20px',
        ));

        $this->addColumn('conv', array(
            'header' => $this->__('Detail'),
            'type' => 'action',
            'width' => '50px',
            'filter' => false,
            'sortable' => false,
            'actions' => array(array(
                    'url' => $this->getUrl('*/fidelitas_campaigns/conversions', array('id' => '$campaign_id')),
                    'caption' => $this->__('Conversions'),
                )),
            'index' => 'type',
            'sortable' => false
        ));
        /*
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
          )); */

        return parent::_prepareColumns();
    }

    public function getRowUrl($row) {
        return false;
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/campaignsgrid', array('_current' => true));
    }

    public function statsResult($value, $row) {

        return $row->getData('views') . ' / ' . $row->getData('clicks');
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
