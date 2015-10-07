<?php

/**
 * /**
 * Class GH_Regulation_VendorController
 */
class GH_Regulation_Dropship_VendorController
    extends Zolago_Dropship_Controller_Vendor_Abstract
{
    /**
     * Action to confirm accept from mail
     */
    public function confirmRegulationAction()
    {
        $error = null;

        try {
            $params = $this->getRequest()->getParams();
            $this->_doConfirmAction($params);
        } catch (Mage_Core_Exception $e) {
            $error = $e->getMessage();
        } catch (Exception $e) {
            Mage::logException($e);
            $error = Mage::helper('ghregulation')
                ->__('Error: System error during request');
        }

        if ($error) {
            $this->_getSession()->addError($error);
        } else {
            $this->_getSession()->addSuccess(Mage::helper('ghregulation')
                ->__('Rules confirmed'));
        }
        return $this->_redirect('udropship/vendor/login');

        if (true) {
            $this->_redirect('*/*/accept');
        }
    }

    protected function _doConfirmAction($params) {

        if (empty($params['token'])) {
            Mage::throwException($this->__('Error: Empty token'));
        }
    }

    public function acceptAction()
    {
        $this->_renderPage();
    }
}