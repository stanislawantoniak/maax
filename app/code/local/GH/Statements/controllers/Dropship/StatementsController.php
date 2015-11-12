<?php

/**
 * Billing and statements controller
 * 1) Sledzenie salda / Balance tracking
 * 2) Rozliczenia okresowe / Periodic statements
 * 3) Faktury / Invoices
 *
 * Class GH_Statements_Dropship_StatementsController
 */
class GH_Statements_Dropship_StatementsController extends Zolago_Dropship_Controller_Vendor_Abstract {

    /**
     * Sledzenie salda / Balance tracking
     */
    public function balanceAction() {
        Mage::register('as_frontend', true);// Tell block class to use regular URL's
        $this->_renderPage(array('default', 'formkey', 'adminhtml_head'), 'statements-balance');
        /** @see app/design/frontend/base/default/template/ghstatements/dropship/balance.phtml */
    }

    /**
     * Rozliczenia okresowe / Periodic statements
     */
    public function periodicAction() {
        Mage::register('as_frontend', true);// Tell block class to use regular URL's
        $this->_renderPage(array('default', 'formkey', 'adminhtml_head'), 'statements-periodic');
        /** @see app/design/frontend/base/default/template/ghstatements/dropship/periodic.phtml */
    }

    /**
     * Faktury / Invoices
     */
    public function invoicesAction() {
        Mage::register('as_frontend', true);// Tell block class to use regular URL's
        $this->_renderPage(array('default', 'formkey', 'adminhtml_head'), 'statements-invoices');
        /** @see app/design/frontend/base/default/template/ghstatements/dropship/invoices.phtml */
    }

    /**
     * download pdf invoice from wfirma
     */

    public function downloadAction() {
        $id = $this->getRequest()->get('id');
        $vendor = Mage::getSingleton('udropship/session')->getVendor();
        if ((!$vendor) || (!$vendor->getId())) {
            Mage::throwException('Vendor not logged');
        }
        $hlp = Mage::helper('zolagopayment');
        try {
            Mage::helper('ghwfirma')->getVendorInvoice($vendor,$id);
        } catch(GH_Wfirma_Exception $e) {
            $this->_getSession()->addError($wfirmaHlp->__($e->getMessage()));
            return $this->_redirectReferer();
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($hlp->__($e->getMessage()));
            return $this->_redirectReferer();
        } catch (Exception $e) {
            $this->_getSession()->addError(Mage::helper('zolagopayment')->__("Some error occurred!"));
            Mage::logException($e);
        }
        return true;

    }

}


