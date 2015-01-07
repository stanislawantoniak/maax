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
class Licentia_Fidelitas_Block_Adminhtml_Links_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('links_grid');
        $this->setDefaultSort('link_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('fidelitas/links')
                ->getResourceCollection();

        if ($id = $this->getRequest()->getParam('id')) {
            $children = Mage::getModel('fidelitas/campaigns')->getChildrenCampaigns($id)->getAllIds();
            $children[] = $id;
            $collection->addFieldToFilter('campaign_id', array('in' => $children));
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {

        $this->addColumn('link_id', array(
            'header' => $this->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'link_id',
        ));

        $this->addColumn('campaign_id', array(
            'header' => $this->__('Campaign'),
            'index' => 'campaign_id',
            'type' => 'options',
            'options' => Mage::getModel('fidelitas/campaigns')->toFormValues(),
        ));

        $this->addColumn('link', array(
            'header' => $this->__('Url'),
            'index' => 'link',
        ));

        $this->addColumn('clicks', array(
            'header' => $this->__('Clicks'),
            'index' => 'clicks',
            'frame_callback' => array($this, 'linkResult'),
        ));

        $this->addExportType('*/*/exportCsv', $this->__('CSV'));
        $this->addExportType('*/*/exportXml', $this->__('Excel XML'));

        return parent::_prepareColumns();
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    public function linkResult($value, $row) {

        if ((int) $value > 0) {
            $url = $this->getUrl('*/fidelitas_urls/', array('id' => $row->getData('link_id')));
            return'<a href="' . $url . '">' . $value . '</a>';
        }

        return $value;
    }

}
