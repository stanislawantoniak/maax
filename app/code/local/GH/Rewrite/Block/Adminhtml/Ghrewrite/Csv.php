<?php

class GH_Rewrite_Block_Adminhtml_Ghrewrite_Csv extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        /** @var GH_Rewrite_Helper_Data $rewriteHelper */
        $rewriteHelper = Mage::helper('ghrewrite');

        $this->_blockGroup = 'ghrewrite';
        $this->_controller = 'adminhtml_ghrewrite';
        $this->_headerText = $rewriteHelper->__('GH Rewrite CSV Loader');
        $this->_mode       = 'csv';

        $this->_removeButton('add');
    }
}
