<?php

/**
 * Controller for grid with Modago api logs
 *
 * Class Modago_Integrator_Adminhtml_Modagoapi_LogController
 */
class Modago_Integrator_Adminhtml_Modagoapi_LogController extends Mage_Adminhtml_Controller_Action {

	public function indexAction() {
		/** @var Modago_Integrator_Helper_Api $helper */
		$helper = Mage::helper('modagointegrator/api');

		$this->_title($helper->__('Modago integrator logs'));
		$this->loadLayout();
		$this->_setActiveMenu('sales/sales');
		$this->_addContent($this->getLayout()->createBlock('modagointegrator/adminhtml_modagoapi_log'));
		$this->renderLayout();
	}

	public function gridAction() {
		$this->loadLayout();
		$this->getResponse()->setBody(
			$this->getLayout()->createBlock('modagointegrator/adminhtml_modagoapi_log_grid')->toHtml()
		);
	}
}