<?php

/**
 * Licentia Fidelitas - Advanced Email and SMS Marketing Automation for E-Goi
 *
 * NOTICE OF LICENSE
 * This source file is subject to the Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International
 * It is available through the world-wide-web at this URL:
 * http://creativecommons.org/licenses/by-nc-nd/4.0/
 *
 * @title      Advanced Email and SMS Marketing Automation
 * @category   Marketing
 * @package    Licentia
 * @author     Bento Vilas Boas <bento@licentia.pt>
 * @copyright  Copyright (c) 2012 Licentia - http://licentia.pt
 * @license    Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International
 */
class Licentia_Fidelitas_Adminhtml_Fidelitas_ReportsController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('fidelitas/reports');

        $auth = Mage::getModel('fidelitas/egoi')->validateEgoiEnvironment();
        if (!$auth) {
            $this->_redirect('adminhtml/fidelitas_account/new');
        }
        return $this;
    }

    public function indexAction() {
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('fidelitas/adminhtml_reports'));
        $this->renderLayout();
    }

    public function detailAction() {

        $this->_title($this->__('E-Goi'))->_title($this->__('Reports'))->_title($this->__('Detail'));
        $id = $this->getRequest()->getParam('id');

        $camp = Mage::getModel('fidelitas/campaigns')->load($id);
        $model = Mage::getSingleton('fidelitas/reports')->load($camp->getHash(), 'hash');

        if ($camp->getRecurring() != '0') {
            $this->_redirect('*/fidelitas_campaigns/edit', array('id' => $camp->getId(), 'tab' => 'reports_edit_tabs_children'));
            return;
        }

        $result = Mage::getSingleton('fidelitas/reports')->refresh($camp->getHash(), $id);

        if (!$result) {
            $this->_getSession()->addNotice($this->__('Report not ready yet'));
            $this->_redirect('*/fidelitas_campaigns/');
            return;
        }

        Mage::register('current_report_campaign', $camp);
        Mage::register('current_report', $model);

        $this->_initAction();

        $this->_addContent($this->getLayout()->createBlock('fidelitas/adminhtml_reports_detail'))
                ->_addLeft($this->getLayout()->createBlock('fidelitas/adminhtml_reports_detail_tabs'));
        $this->renderLayout();
    }

}
