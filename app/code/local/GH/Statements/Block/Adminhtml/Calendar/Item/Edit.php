<?php

class GH_Statements_Block_Adminhtml_Calendar_Item_Edit extends GH_Statements_Block_Adminhtml_Calendar_Edit_Abstract
{

    /**
     * @return GH_Statements_Model_Calendar_Item
     */
    public function getModel()
    {
        return Mage::registry('ghstatements_current_calendar_item');
    }

    protected function _prepareLayout()
    {
        $id = $this->getRequest()->getParam('calendar_id');
        $this->setChild('back_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('ghstatements')->__('Back'),
                    'onclick' => "window.location.href = '" . $this->getUrl('*/*/calendar_item',array('id'=>$id)) . "'",
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
                    'onclick' => 'calendarItemControl.save();',
                    'class' => 'save'
                ))
        );
        $this->setChild('delete_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('ghstatements')->__('Delete'),
                    'onclick' => 'calendarItemControl.remove();',
                    'class' => 'delete'
                ))
        );
        return parent::_prepareLayout();
    }

    public function getHeaderText()
    {
        if ($this->getIsNew()) {
            return Mage::helper('ghstatements')->__('Edit event');
        }
        return Mage::helper('ghstatements')->__('New event');
    }

    public function getSaveUrl()
    {
        return $this->getUrl('*/*/calendar_item_save', array("_current" => true));
    }

    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/calendar_item_delete', array("_current" => true));
    }

}
