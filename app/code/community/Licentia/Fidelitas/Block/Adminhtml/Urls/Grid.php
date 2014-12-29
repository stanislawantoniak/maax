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
class Licentia_Fidelitas_Block_Adminhtml_Urls_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('events_grid');
        $this->setDefaultSort('url_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('fidelitas/urls')
                ->getResourceCollection() ;

        if ($id = $this->getRequest()->getParam('id')) {
            $collection->addFieldToFilter('link_id', $id);
        }
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {

        $this->addColumn('url_id', array(
            'header' => $this->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'url_id',
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
            'type' => 'concat',
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

        $this->addColumn('visit_at', array(
            'header' => $this->__('Visit time'),
            'align' => 'left',
            'width' => '170px',
            'type' => 'datetime',
            'index' => 'visit_at',
        ));

        $this->addExportType('*/*/exportCsv', $this->__('CSV'));
        $this->addExportType('*/*/exportXml', $this->__('Excel XML'));

        return parent::_prepareColumns();
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
