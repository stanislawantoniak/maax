<?php

/**
 * Class Zolago_Payment_Adminhtml_Vendor_PaymentController
 */
class Zolago_Payment_Adminhtml_Vendor_PaymentController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id', null);
        $this->_initModel($id);

        $this->loadLayout();
        $this->renderLayout();
    }


    /**
     * @param $modelId
     * @return Zolago_Payment_Model_Vendor_Payment
     */
    protected function _initModel($modelId)
    {
        if (Mage::registry('zolagopayment_current_payment') instanceof Zolago_Payment_Model_Vendor_Payment) {
            return Mage::registry('zolagopayment_current_payment');
        }

        $model = Mage::getModel("zolagopayment/vendor_payment");
        /* @var $model Zolago_Payment_Model_Vendor_Payment */
        if ($modelId) {
            $model->load($modelId);
        }

        Mage::register('zolagopayment_current_payment', $model);
        return $model;
    }
}