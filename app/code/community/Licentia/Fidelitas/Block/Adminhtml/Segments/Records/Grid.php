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
class Licentia_Fidelitas_Block_Adminhtml_Segments_Records_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('importerGrid');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection() {

        $id = $this->getRequest()->getParam('id');

        $collection = Mage::getModel('fidelitas/segments_list')
                ->getCollection()
                ->addFieldToFilter('segment_id', $id);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {


        $this->addColumn('listnum', array(
            'header' => $this->__('List Name'),
            'type' => 'options',
            'align' => 'left',
            'options' => Mage::getModel('fidelitas/lists')->getOptionArray(),
            'index' => 'listnum',
        ));


        $this->addColumn('first_name', array(
            'header' => $this->__('First Name'),
            'align' => 'left',
            'index' => 'first_name',
        ));


        $this->addColumn('last_name', array(
            'header' => $this->__('Last Name'),
            'align' => 'left',
            'index' => 'last_name',
        ));


        $this->addColumn('email', array(
            'header' => $this->__('Email'),
            'align' => 'left',
            'index' => 'email',
        ));


        $this->addColumn('cellphone', array(
            'header' => $this->__('Cellphone'),
            'align' => 'left',
            'index' => 'cellphone',
        ));

        $this->addColumn('conversions_number', array(
            'header' => $this->__('Conversions'),
            'align' => 'left',
            'width' => '80px',
            'type' => 'number',
            'index' => 'conversions_number',
        ));
        /*
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
         */

        $this->addColumn('customer_link', array(
            'header' => $this->__('View'),
            'align' => 'center',
            'index' => 'customer_id',
            'width' => '80px',
            'frame_callback' => array($this, 'customerResult'),
            'is_system' => true,
            'sortable' => false
        ));

        $this->addExportType('*/*/exportCsv', $this->__('CSV'));
        $this->addExportType('*/*/exportXml', $this->__('Excel XML'));

        return parent::_prepareColumns();
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/recordsgrid', array('_current' => true));
    }

    public function getRowUrl($row) {
        return false;
    }

    public function customerResult($value) {

        if ($value > 0) {
            $url = $this->getUrl('*/customer/edit', array('id' => $value));
            return'<a href="' . $url . '">' . $this->__('Customer') . '</a>';
        }

        return $this->__('N/A');
    }

}
