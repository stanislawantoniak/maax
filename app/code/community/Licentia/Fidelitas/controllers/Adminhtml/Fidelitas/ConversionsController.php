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
class Licentia_Fidelitas_Adminhtml_Fidelitas_ConversionsController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('fidelitas/conversions');

        $auth = Mage::getModel('fidelitas/egoi')->validateEgoiEnvironment();
        if (!$auth) {
            $this->_redirect('adminhtml/fidelitas_account/new');
        }
        return $this;
    }

    public function consegmentsAction() {

        $this->_title($this->__('E-Goi'))->_title($this->__('Segments Conversions'));
        if ($id = $this->getRequest()->getParam('id')) {
            $segment = Mage::getModel('fidelitas/segments')->load($id);

            if ($segment->getId()) {
                Mage::register('current_segment', $segment);
                $this->_title($segment->getName());
            }
        }
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('fidelitas/adminhtml_consegments'));
        $this->renderLayout();
    }

    public function conseggridAction() {

        if ($id = $this->getRequest()->getParam('id')) {
            $segment = Mage::getModel('fidelitas/segments')->load($id);
            if ($segment->getId()) {
                Mage::register('current_segment', $segment);
                $this->_title($segment->getName());
            }
        }
        $this->loadLayout();
        $this->renderLayout();
    }

    public function indexAction() {

        $this->_title($this->__('E-Goi'))->_title($this->__('Conversions'));

        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('fidelitas/adminhtml_conversions'));
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
        $fileName = 'conversions.csv';
        $content = $this->getLayout()->createBlock('fidelitas/adminhtml_conversions_grid')
                ->getCsvFile();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export customer grid to XML format
     */
    public function exportXmlAction() {
        $fileName = 'conversions.xml';
        $content = $this->getLayout()->createBlock('fidelitas/adminhtml_conversions_grid')
                ->getExcelFile();

        $this->_prepareDownloadResponse($fileName, $content);
    }

}
