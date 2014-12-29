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
class Licentia_Fidelitas_Adminhtml_Fidelitas_UrlsController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('fidelitas/events');

        $auth = Mage::getModel('fidelitas/egoi')->validateEgoiEnvironment();
        if (!$auth) {
            $this->_redirect('adminhtml/fidelitas_account/new');
        }
        return $this;
    }

    public function indexAction() {

        $this->_title($this->__('E-Goi'))->_title($this->__('Urls'));

        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('fidelitas/adminhtml_urls'));
        $this->renderLayout();
    }

    public function gridAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Export customer grid to CSV format
     */
    public function exportCsvAction() {
        $fileName = 'urls.csv';
        $content = $this->getLayout()->createBlock('fidelitas/adminhtml_urls_grid')
                ->getCsvFile();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export customer grid to XML format
     */
    public function exportXmlAction() {
        $fileName = 'urls.xml';
        $content = $this->getLayout()->createBlock('fidelitas/adminhtml_urls_grid')
                ->getExcelFile();

        $this->_prepareDownloadResponse($fileName, $content);
    }

}
