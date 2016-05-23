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
	 * @param null|array $handles
	 * @param null|string $active
	 * @param null|string $title
	 */
	protected function _renderPage($handles = null, $active = null, $title = null) {
		/** @var ZolagoOs_OutsideStore_Helper_Data $commonHlp */
		$commonHlp = Mage::helper("zosoutsidestore");
		if (!$commonHlp->useGalleryConfiguration()) {
			$this->_redirect('udropship/vendor/');
			return;
		}
		parent::_renderPage($handles, $active, $title);
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
        /** @var Zolago_Payment_Helper_Data $zpHlp */
        $zpHlp = Mage::helper('zolagopayment');
        /** @var GH_Wfirma_Helper_Data $wfirmaHlp */
        $wfirmaHlp = Mage::helper('ghwfirma');
        try {
            $wfirmaHlp->getVendorInvoice($vendor,$id);
        } catch(GH_Wfirma_Exception $e) {
            $this->_getSession()->addError(Mage::helper("ghwfirma")->__($e->getMessage()));
            return $this->_redirectReferer();
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($zpHlp->__($e->getMessage()));
            return $this->_redirectReferer();
        } catch (Exception $e) {
            $this->_getSession()->addError($zpHlp->__("Some error occurred!"));
            Mage::logException($e);
        }
        return true;

    }

	public function downloadStatementAction() {
		$id = $this->getRequest()->get('id');

		/** @var Zolago_Dropship_Model_Vendor $vendor */
		$vendor = Mage::getSingleton('udropship/session')->getVendor();
		//validate login
		if ((!$vendor) || (!$vendor->getId())) {
			Mage::throwException('Vendor not logged');
		}

		/** @var GH_Statements_Model_Statement $statement */
		$statement = Mage::getModel('ghstatements/statement')->load($id);
		//validate statement
		if(!$statement->getId() || $statement->getVendorId() !=  $vendor->getId()) {
			Mage::throwException('Statement does not exist');
		}

		/** @var GH_Statements_Helper_Vendor_Statement $hlp */
		$hlp = Mage::helper('ghstatements/vendor_statement');

		$hlp->downloadStatementPdf($statement);
	}
}