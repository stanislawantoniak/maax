<?php

class GH_Statements_Block_Adminhtml_Vendor_Statements_Edit extends Mage_Adminhtml_Block_Widget
{

    public function __construct()
    {
        parent::__construct();
    }
    /**
     * @return GH_Statements_Model_Statement
     */
    public function getModel()
    {
        return Mage::registry('ghstatements_current_statement');
    }

    protected function _prepareLayout()
    {
        $this->setChild('back_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('ghstatements')->__('Back'),
                    'onclick' => "window.location.href = '" . $this->getUrl('*/*') . "'",
                    'class' => 'back'
                ))
        );
        $this->setChild('reset_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('ghstatements')->__('Reset'),
                    'onclick' => 'window.location.href = window.location.href'
                ))
        );
        $this->setChild('save_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('ghstatements')->__('Save'),
                    'onclick' => 'ghStatementsControl.save();',
                    'class' => 'save'
                ))
        );
        $this->setChild('delete_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('ghstatements')->__('Delete Statement'),
                    'onclick' => 'ghStatementsControl.remove();',
                    'class' => 'delete'
                ))
        );
        return parent::_prepareLayout();
    }

    public function getBackButtonHtml()
    {
        return $this->getChildHtml('back_button');
    }

    public function getResetButtonHtml()
    {
        return $this->getChildHtml('reset_button');
    }

    public function getSaveButtonHtml()
    {
        return $this->getChildHtml('save_button');
    }

    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_button');
    }

    public function getIsNew()
    {
        return $this->getModel()->getId();
    }

    public function getHeaderText()
    {
        return Mage::helper('ghstatements')->__('Statement');
    }

    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', array("_current" => true));
    }

    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', array("_current" => true));
    }

}
