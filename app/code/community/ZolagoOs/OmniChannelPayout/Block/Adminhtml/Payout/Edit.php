<?php
/**
  
 */

class ZolagoOs_OmniChannelPayout_Block_Adminhtml_Payout_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'id';
        $this->_blockGroup = 'udpayout';
        $this->_controller = 'adminhtml_payout';

        if ($this->getRequest()->getParam($this->_objectId)) {
            $this->_updateButton('delete', 'label', Mage::helper('udropship')->__('Delete Payout'));
            $model = Mage::getModel('udpayout/payout')
                ->load($this->getRequest()->getParam($this->_objectId));
            Mage::register('payout_data', $model);
            if ($model->getPayoutStatus() != ZolagoOs_OmniChannelPayout_Model_Payout::STATUS_PAID
                && $model->getPayoutStatus() != ZolagoOs_OmniChannelPayout_Model_Payout::STATUS_PAYPAL_IPN
                && $model->getPayoutStatus() != ZolagoOs_OmniChannelPayout_Model_Payout::STATUS_CANCELED
                && $model->getPayoutStatus() != ZolagoOs_OmniChannelPayout_Model_Payout::STATUS_HOLD
            ) {
                $this->_addButton('save_pay', array(
                    'label'     => Mage::helper('adminhtml')->__('Save and Pay'),
                    'onclick'   => "\$('pay_flag').value=1; editForm.submit();",
                    'class'     => 'save',
                ), 1);
            }
        } else {
            $this->_updateButton('save', 'label', Mage::helper('udropship')->__('Create Payout(s)'));
        }
    }

    public function getHeaderText()
    {
        return Mage::helper('udpayout')->__('Payout');
    }
}
