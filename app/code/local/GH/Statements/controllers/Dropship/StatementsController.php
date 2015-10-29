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
        $this->_renderPage(null, 'statements-balance');
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
        $this->_renderPage(null, 'statements-invoices');
        /** @see app/design/frontend/base/default/template/ghstatements/dropship/invoices.phtml */
    }
}


