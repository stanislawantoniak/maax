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
class Licentia_Fidelitas_Block_Adminhtml_Lists_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('importerGrid');
        $this->setDefaultSort('list_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection() {

        $collection = Mage::getModel('fidelitas/lists')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('list_id', array(
            'header' => $this->__('ID'),
            'width' => '50px',
            'index' => 'list_id',
        ));

        $this->addColumn('title', array(
            'header' => $this->__('List Title'),
            'align' => 'left',
            'index' => 'title',
        ));

        $this->addColumn('internal_name', array(
            'header' => $this->__('Internal Name'),
            'align' => 'left',
            'index' => 'internal_name',
        ));

        $this->addColumn('store_ids', array(
            'header' => $this->__('Stores'),
            'align' => 'left',
            'separator' => '<br>',
            'filter' => false,
            'sortable' => false,
            'index' => 'store_ids',
            'renderer' => 'Licentia_Fidelitas_Block_Adminhtml_Widget_Grid_Column_Renderer_Stores',
        ));

        $this->addColumn('subs_activos', array(
            'header' => $this->__('Subscribers'),
            'width' => '60px',
            'type' => 'number',
            'index' => 'subs_activos',
        ));

        $this->addColumn('is_active', array(
            'header' => $this->__('Is Active'),
            'width' => '60px',
            'type' => 'options',
            'options' => array('0' => $this->__('No'), '1' => $this->__('Yes')),
            'index' => 'is_active',
        ));


        /*
          $this->addColumn('subs_total', array(
          'header' => $this->__('Total Subscribers'),
          'type' => 'number',
          'width' => '60px',
          'index' => 'subs_total',
          ));
         */

        $this->addColumn('action', array(
            'header' => $this->__('Action'),
            'type' => 'action',
            'width' => '150px',
            'filter' => false,
            'sortable' => false,
            'actions' => array(array(
                    'url' => $this->getUrl('adminhtml/fidelitas_subscribers/index', array('listnum' => '$listnum')),
                    'caption' => $this->__('View Subscribers'),
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
