<?php

class GH_Rewrite_Block_Adminhtml_Ghrewrite extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();

        /** @var GH_Rewrite_Helper_Data $rewriteHelper */
        $rewriteHelper = Mage::helper('ghrewrite');

        $this->_blockGroup = 'ghrewrite';
        $this->_controller = 'adminhtml_ghrewrite';
        $this->_headerText = $rewriteHelper->__('GH Rewrite');

        $this->_removeButton('add');

        $this->_addButton('loadcsv', array(
            'label'     => $rewriteHelper->__('Load CSV file'),
            'onclick'   => 'setLocation(\'' . $this->getLoadCsvUrl() .'\')',
            'class'     => 'add',
        ));
    }

    public function getLoadCsvUrl()
    {
        return $this->getUrl('*/*/loadcsv');
    }
}