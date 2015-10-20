<?php

/**
 * Class Zolago_Payment_Block_Adminhtml_Vendor_Payment_Edit
 */
class Zolago_Payment_Block_Adminhtml_Vendor_Payment_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct()
    {
        //$this->_objectId = 'id';

        $this->_blockGroup = 'zolagopayment';
        $this->_controller = 'adminhtml/vendor_payment';

        $modelTitle = $this->_getModelTitle();
        $this->_updateButton('save', 'label', $this->_getHelper()->__("Save $modelTitle"));
        $this->_addButton('saveandcontinue', array(
            'label' => $this->_getHelper()->__('Save and Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
        ), -100);

        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
        parent::__construct();
    }

    protected function _getHelper()
    {
        return Mage::helper('zolagopayment');
    }

    protected function _getModel()
    {
        return Mage::registry('zolagopayment_current_payment');
    }

    protected function _getModelTitle()
    {
        return $this->_getHelper()->__("Vendor Payment");
    }

    public function getHeaderText()
    {
        $model = $this->_getModel();
        $modelTitle = $this->_getModelTitle();

        if ($model && $model->getId()) {
            return $this->_getHelper()->__("Edit $modelTitle (ID: {$model->getId()})");
        } else {
            return $this->_getHelper()->__("New $modelTitle");
        }
    }


    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/index');
    }

    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', array($this->_objectId => $this->getRequest()->getParam($this->_objectId)));
    }

    /**
     * Get form save URL
     *
     * @deprecated
     * @see getFormActionUrl()
     * @return string
     */
    public function getSaveUrl()
    {
        $this->setData('form_action_url', 'save');
        Mage::log($this->getFormActionUrl());
        return $this->getFormActionUrl();
    }


}
