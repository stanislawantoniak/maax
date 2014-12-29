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
class Licentia_Fidelitas_Block_Adminhtml_Splits_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('campaign_grid');
        $this->setDefaultSort('split_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('fidelitas/splits')
                ->getResourceCollection();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('split_id', array(
            'header' => $this->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'split_id',
        ));

        $this->addColumn('name', array(
            'header' => $this->__('Name'),
            'align' => 'left',
            'index' => 'name',
        ));

        $this->addColumn('deploy_at', array(
            'header' => $this->__('Send At'),
            'align' => 'left',
            'type' => 'datetime',
            'width' => '180px',
            'index' => 'deploy_at',
        ));

        $this->addColumn('send_at', array(
            'header' => $this->__('General Send At'),
            'align' => 'left',
            'type' => 'datetime',
            'width' => '180px',
            'index' => 'send_at',
        ));

        $this->addColumn('views_a', array(
            'header' => $this->__('Views A | B'),
            'align' => 'left',
            'index' => array('views_a', 'views_b'),
            'type' => 'text',
            'renderer' => 'Licentia_Fidelitas_Block_Adminhtml_Widget_Grid_Column_Renderer_Concat',
            'separator' => ' | ',
            'filter_index' => "CONCAT(views_a, ' | ', views_b)",
        ));

        $this->addColumn('clicks_a', array(
            'header' => $this->__('Clicks A | B'),
            'align' => 'left',
            'index' => array('clicks_a', 'clicks_b'),
            'type' => 'text',
            'renderer' => 'Licentia_Fidelitas_Block_Adminhtml_Widget_Grid_Column_Renderer_Concat',
            'separator' => ' | ',
            'filter_index' => "CONCAT(clicks_a' | ', clicks_b)",
        ));

        $this->addColumn('conversions_a', array(
            'header' => $this->__('Conversions A | B'),
            'align' => 'left',
            'index' => array('conversions_a', 'conversions_b'),
            'type' => 'text',
            'renderer' => 'Licentia_Fidelitas_Block_Adminhtml_Widget_Grid_Column_Renderer_Concat',
            'separator' => ' | ',
            'filter_index' => "CONCAT(conversions_a, ' | ',conversions_b)",
        ));

        $this->addColumn('active', array(
            'header' => $this->__('Status'),
            'align' => 'left',
            'width' => '100px',
            'index' => 'active',
            'type' => 'options',
            'options' => array('0' => $this->__('Inactive'), '1' => $this->__('Active')),
        ));

        $this->addColumn('closed', array(
            'header' => $this->__('Finished'),
            'align' => 'left',
            'width' => '100px',
            'index' => 'closed',
            'type' => 'options',
            'options' => array('0' => $this->__('No'), '1' => $this->__('Yes')),
        ));

        $this->addColumn('winner', array(
            'header' => $this->__('Winner'),
            'align' => 'left',
            'width' => '100px',
            'index' => 'winner',
            'type' => 'options',
            'options' => Mage::getModel('fidelitas/splits')->getWinnerOptions(),
        ));

        $this->addColumn('closed_a', array(
            'header' => $this->__('View'),
            'align' => 'left',
            'width' => '180px',
            'filter' => false,
            'sortable' => false,
            'frame_callback' => array($this, 'serviceResult'),
            'index' => 'closed',
        ));

        return parent::_prepareColumns();
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    public function serviceResult($value, $row) {

        if ((int) $row->getClosed() == 1) {

            $campaign = Mage::getModel('fidelitas/splits')->getFinalCampaign($row, 'campaign_id');

            if (!$campaign->getData('campaign_id')) {
                return 'N/D';
            }

            $url = $this->getUrl('*/fidelitas_campaigns/edit', array('id' => $campaign->getData('campaign_id')));
            return'<a href="' . $url . '">Final Campaign [' . $campaign->getData('campaign_id') . ']</a>';
        }

        return 'N/D';
    }

}
