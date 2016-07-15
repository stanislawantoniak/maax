<?php

/**
 * Class Zolago_Adminhtml_Block_Sales_Transactions_Edit
 */
class Zolago_Adminhtml_Block_Sales_Transactions_Edit extends Mage_Adminhtml_Block_Widget
{

    /**
     * @return Mage_Sales_Model_Order_Payment_Transaction
     */
    public function getModel()
    {
        return Mage::registry('current_transaction');
    }

    protected function _prepareLayout()
    {
        $this->setChild('back_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('sales')->__('Back'),
                    'onclick' => "window.location.href = '" . $this->getUrl('*/*') . "'",
                    'class' => 'back'
                ))
        );

        $this->setChild('save_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('sales')->__('Save'),
                    'onclick' => 'formControl.save();',
                    'class' => 'save'
                ))
        );
        $this->setChild('delete_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('sales')->__('Reject'),
                    'onclick' => 'formControl.remove();',
                    'class' => 'delete'
                ))
        );
        return parent::_prepareLayout();
    }

    public function getBackButtonHtml()
    {
        return $this->getChildHtml('back_button');
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

    /**
     * @return string
     */
    public function _getModelName()
    {
        return Mage::helper("sales")->__("Bank Payment");
    }

    public function getHeaderText()
    {
        if ($this->getIsNew()) {
            return Mage::helper('sales')->__('Edit Bank Payment');
        }
        return Mage::helper('sales')->__('Add New Bank Payment');
    }

    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', array("_current" => true));
    }

    public function getRejectUrl()
    {
        return $this->getUrl('*/*/reject', array("_current" => true));
    }

}
