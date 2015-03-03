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
class Licentia_Fidelitas_Block_Adminhtml_Campaigns_Grid extends Mage_Adminhtml_Block_Widget_Grid {

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
                ->addFieldToFilter('hidden', '0')
                ->addFieldToFilter('autoresponder', array('null' => 'arroz'))
                ->addFieldToFilter('parent_id', array('null' => 'arroz'));

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
        /*
          $this->addColumn('subject', array(
          'header' => $this->__('Subject'),
          'align' => 'left',
          'index' => 'subject',
          ));
         */
        $this->addColumn('recurring_next_run', array(
            'header' => $this->__('Deploy Date/Next Run'),
            'align' => 'left',
            'width' => '180px',
            'type' => 'datetime',
            'default' => '-NA-',
            'index' => 'recurring_next_run',
        ));

        $this->addColumn('local_status', array(
            'header' => $this->__('Status'),
            'align' => 'left',
            'width' => '80px',
            'frame_callback' => array($this, 'serviceResult'),
            'index' => 'local_status',
        ));

        $this->addColumn('recurring', array(
            'header' => $this->__('Recurring'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'recurring',
            'type' => 'options',
            'options' => Licentia_Fidelitas_Model_Campaigns::getCronList(),
        ));

        $this->addColumn('recurring_last_run', array(
            'header' => $this->__('Last Run'),
            'align' => 'left',
            'width' => '170px',
            'default' => $this->__('-Not deployed yet-'),
            'index' => 'recurring_last_run',
            'type' => 'datetime',
        ));

        $this->addColumn('conversions_number', array(
            'header' => $this->__('Conversions'),
            'align' => 'left',
            'width' => '80px',
            'type' => 'number',
            'index' => 'conversions_number',
            'frame_callback' => array($this, 'conversionsResult'),
        ));

        $this->addColumn('conversions_amount', array(
            'header' => $this->__('Conv. Amount'),
            'align' => 'left',
            'width' => '80px',
            'type' => 'currency',
            'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
            'index' => 'conversions_amount',
        ));

        /*
          $this->addColumn('clicks', array(
          'header' => $this->__('Sent/Views/Clicks'),
          'sortable' => true,
          'index' => array('sent', 'unique_views', 'unique_clicks'),
          'type' => 'concat',
          'separator' => ' / ',
          'filter_index' => "CONCAT(sent, ' / ',unique_univiews, ' / ', unique_clicks)",
          'width' => '140px',
          )
          );

         */

        $this->addColumn('sent', array(
            'header' => $this->__('Sent'),
            'align' => 'right',
            'index' => 'sent',
            'width' => '20px',
        ));

        $this->addColumn('unique_views', array(
            'header' => $this->__('U. Views'),
            'align' => 'right',
            'index' => 'unique_views',
            'width' => '20px',
        ));

        $this->addColumn('unique_clicks', array(
            'header' => $this->__('U. Clicks'),
            'align' => 'right',
            'index' => 'unique_clicks',
            'frame_callback' => array($this, 'linkResult'),
            'width' => '20px',
        ));
        /*
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
         */
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
        return $this->getUrl('*/*/edit', array('id' => $row->getCampaignId()));
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/grid', array('_current' => true));
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

    public function linkResult($value, $row) {

        if ((int) $value > 0) {
            $url = $this->getUrl('*/fidelitas_links/', array('id' => $row->getData('campaign_id')));
            return'<a href="' . $url . '">' . $value . ' (Detail)</a>';
        }

        return $value;
    }

    public function conversionsResult($value, $row) {

        if ((int) $value > 0) {
            $url = $this->getUrl('*/*/conversions', array('id' => $row->getData('campaign_id')));
            return'<a href="' . $url . '">' . $value . ' (Detail)</a>';
        }

        return $value;
    }

}
