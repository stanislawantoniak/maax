<?php

/**
 * Class Zolago_Payment_Block_Adminhtml_Vendor_Payment_Edit
 */
class Zolago_Payment_Block_Adminhtml_Vendor_Payment_Edit extends Mage_Adminhtml_Block_Widget
{

    /**
     * @return Zolago_Payment_Model_Vendor_Payment
     */
    public function getModel()
    {
        return Mage::registry('zolagopayment_current_payment');
    }

    protected function _prepareLayout()
    {
        $this->setChild('back_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('zolagopayment')->__('Back'),
                    'onclick' => "window.location.href = '" . $this->getUrl('*/*') . "'",
                    'class' => 'back'
                ))
        );

        $this->setChild('save_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('zolagopayment')->__('Save'),
                    'onclick' => 'formControl.save();',
                    'class' => 'save'
                ))
        );
        $this->setChild('delete_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('zolagopayment')->__('Delete'),
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
        return Mage::helper("zolagopayment")->__("Vendor Payment");
    }

    public function getHeaderText()
    {
        if ($this->getIsNew()) {
            return Mage::helper('zolagopayment')->__('Edit %s Payment', $this->getModel()->getVendor()->getVendorName());
        }
        return Mage::helper('zolagopayment')->__('New Vendor Payment');
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
