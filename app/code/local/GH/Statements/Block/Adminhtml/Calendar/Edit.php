<?php

class GH_Statements_Block_Adminhtml_Calendar_Edit extends Mage_Adminhtml_Block_Widget
{

    /**
     * @return GH_Dhl_Model_Dhl
     */
    public function getModel()
    {
        return Mage::registry('ghstatements_current_calendar');
    }

    protected function _prepareLayout()
    {
        $this->setChild('back_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('ghstatements')->__('Back'),
                    'onclick' => "window.location.href = '" . $this->getUrl('*/*/calendar') . "'",
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
                    'onclick' => 'calendarControl.save();',
                    'class' => 'save'
                ))
        );
        $this->setChild('delete_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('ghstatements')->__('Delete'),
                    'onclick' => 'calendarControl.remove();',
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
        if ($this->getIsNew()) {
            return Mage::helper('ghstatements')->__('Edit calendar');
        }
        return Mage::helper('ghstatements')->__('New calendar');
    }

    public function getSaveUrl()
    {
        return $this->getUrl('*/*/calendar_save', array("_current" => true));
    }

    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/calendar_delete', array("_current" => true));
    }

}
