<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */


class Magpleasure_Blog_Block_Adminhtml_Import extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Helper
     * @return Magpleasure_Blog_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('mpblog');
    }

    public function __construct()
    {
        parent::__construct();
        $this->_objectId = null;
        $this->_blockGroup = null;
        $this->_controller = null;
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $form = $this->getLayout()->createBlock('mpblog/adminhtml_import_form');
        $this->setChild('form', $form);
    }

    protected function _beforeToHtml()
    {
        $this
            ->_removeButton('back')
            ->_removeButton('reset')
            ->_removeButton('save')
            ->addButton('import', array(
                'label'     => $this->__('Import'),
                'onclick'   => "editForm.submit();",
                'class'     => 'save',
            ))
            ;
        parent::_beforeToHtml();
    }

    public function getLabel()
    {
        return (string)$this->_helper()->getConfigValue($this->getImportType(), 'label');
    }

    public function getHeaderText()
    {
        return $this->__("Import - %s", $this->getLabel());
    }

}