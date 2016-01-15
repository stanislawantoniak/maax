<?php

/**
 * Grid for logs from Modago Api
 *
 * Class Modago_Integrator_Block_Adminhtml_Modagoapi_Log_Grid
 */
class Modago_Integrator_Block_Adminhtml_Modagoapi_Log_Grid extends Mage_Adminhtml_Block_Widget_Grid {

	public function __construct() {
		parent::__construct();
		$this->setId('modagointegrator_modagoapi_log_grid');
		$this->setDefaultSort('id');
		$this->setSaveParametersInSession(true);
		$this->setUseAjax(true);
	}

	protected function _prepareCollection() {
		/** @var Modago_Integrator_Model_Resource_Log_Collection $collection */
		$collection = Mage::getResourceModel('modagointegrator/log_collection');

		$this->setCollection($collection);
		parent::_prepareCollection();
		return $this;
	}

	protected function _prepareColumns() {
		/** @var Modago_Integrator_Helper_Api $helper */
		$helper = Mage::helper('modagointegrator/api');

		$this->addColumn('id', array(
			'header' => $helper->__('ID'),
			'index'  => 'id',
			'width'  => '100px',
		));

		$this->addColumn('date', array(
			'header' => $helper->__('Event date'),
			'type'   => 'text',
			'index'  => 'date',
			'width'  => '150px',
		));

		$this->addColumn('text', array(
			'header' => $helper->__('Message'),
			'index'  => 'text'
		));

		return parent::_prepareColumns();
	}

	public function getGridUrl() {
		return $this->getUrl('*/*/grid', array('_current' => true));
	}
}