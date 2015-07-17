<?php

class GH_Statements_Block_Adminhtml_Calendar_Edit extends GH_Statements_Block_Adminhtml_Calendar_Edit_Abstract
{

    /**
     * @return GH_Statements_Model_Calendar
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
        $this->setChild('events_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                        'label' => Mage::helper('ghstatements')->__('Edit events'),
                        'onclick' => "window.location.href = '".$this->getUrl('*/*/calendar_item', array("_current" => true))."'",
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

    public function getHeaderText()
    {
        if ($this->getIsNew()) {
            return Mage::helper('ghstatements')->__('Edit calendar');
        }
        return Mage::helper('ghstatements')->__('New calendar');
    }
    public function getEventsButtonHtml() {
        return $this->getChildHtml('events_button');
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
