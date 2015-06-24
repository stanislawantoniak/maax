<?php

class GH_Dhl_Block_Adminhtml_Dhl_Edit extends Mage_Adminhtml_Block_Widget
{

    /**
     * @return GH_Dhl_Model_Dhl
     */
    public function getModel()
    {
        return Mage::registry('ghdhl_current_dhl');
    }

    protected function _prepareLayout()
    {
        $this->setChild('back_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('ghdhl')->__('Back'),
                    'onclick' => "window.location.href = '" . $this->getUrl('*/*') . "'",
                    'class' => 'back'
                ))
        );
        $this->setChild('reset_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('ghdhl')->__('Reset'),
                    'onclick' => 'window.location.href = window.location.href'
                ))
        );
        $this->setChild('save_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('ghdhl')->__('Save'),
                    'onclick' => 'dhlControl.save();',
                    'class' => 'save'
                ))
        );
        $this->setChild('delete_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('ghdhl')->__('Delete'),
                    'onclick' => 'dhlControl.remove();',
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
            return Mage::helper('ghdhl')->__('Edit DHL Account');
        }
        return Mage::helper('ghdhl')->__('New DHL Account');
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
