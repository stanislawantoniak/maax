<?php

/**
 * Block for Grid
 *
 * Class Modago_Integrator_Block_Adminhtml_Modagoapi_log
 */
class Modago_Integrator_Block_Adminhtml_Modagoapi_log extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
		/** @var Modago_Integrator_Helper_Api $helper */
		$helper = Mage::helper('modagointegrator/api');

		$this->_blockGroup = 'modagointegrator';
		$this->_controller = 'adminhtml_modagoapi_log';
		$this->_headerText = $helper->__('Modago integrator logs');

		parent::__construct();
		$this->_removeButton('add');
	}
}