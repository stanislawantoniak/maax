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
class Licentia_Fidelitas_Block_Adminhtml_Reports_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('campaign_grid');
        $this->setDefaultSort('report_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection() {

        $collection = Mage::getModel('fidelitas/reports')
                ->getResourceCollection();

        $collection->getSelect()->joinLeft(Mage::getSingleton('core/resource')->getTableName('fidelitas_campaigns'), Mage::getSingleton('core/resource')->getTableName('fidelitas_campaigns') . '.hash = main_table.hash ', array('*'));

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {

        $this->addColumn('internal_name', array(
            'header' => $this->__('Campaign Internal Name'),
            'width' => '300px',
            'align' => 'left',
            'index' => 'internal_name',
        ));

        $this->addColumn('channel', array(
            'header' => $this->__('Channel'),
            'align' => 'left',
            'index' => 'channel',
            'type' => 'options',
            'options' => Mage::getModel('fidelitas/campaigns')->getChannelsOption(),
        ));

        $this->addColumn('sent', array(
            'header' => $this->__('Sent'),
            'align' => 'left',
            'index' => 'sent',
            'type' => 'number',
        ));

        $this->addColumn('views', array(
            'header' => $this->__('Views'),
            'align' => 'left',
            'index' => 'views',
            'type' => 'number',
        ));

        $this->addColumn('returned', array(
            'header' => $this->__('Returned'),
            'align' => 'left',
            'index' => 'returned',
            'type' => 'number',
        ));

        $this->addColumn('unique_clicks', array(
            'header' => $this->__('Unique Clicks'),
            'align' => 'left',
            'index' => 'unique_clicks',
            'type' => 'number',
        ));

        $this->addColumn('not_opened', array(
            'header' => $this->__('Not Opened'),
            'align' => 'left',
            'index' => 'not_opened',
            'type' => 'number',
        ));

        $this->addColumn('recomendations', array(
            'header' => $this->__('Recommendations'),
            'align' => 'left',
            'index' => 'recomendations',
            'type' => 'number',
        ));


        $this->addColumn('total_country', array(
            'header' => $this->__('Total Countries'),
            'align' => 'left',
            'index' => 'total_country',
            'type' => 'number',
        ));


        $this->addColumn('start', array(
            'header' => $this->__('Sent Date'),
            'align' => 'left',
            'width' => '150px',
            'index' => 'start',
            'type' => 'datetime',
        ));

//
//        $this->addColumn('action', array(
//            'header' => $this->__('Details'),
//            'type' => 'action',
//            'width' => '60px',
//            'filter' => false,
//            'sortable' => false,
//            'actions' => array(array(
//                    'url' => $this->getUrl('adminhtml/fidelitas_reports/detail', array('report_id' => '$report_id')),
//                    'caption' => $this->__('View'),
//            )),
//            'index' => 'type',
//            'sortable' => false
//        ));


        return parent::_prepareColumns();
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    public function getRowUrl($row) {
        return $this->getUrl('adminhtml/fidelitas_reports/detail', array('id' => $row->getId()));
    }

}
