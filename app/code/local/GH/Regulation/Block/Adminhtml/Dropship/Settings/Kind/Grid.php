<?php

/**
 * brand settings grid
 */
class Gh_Regulation_Block_Adminhtml_Dropship_Settings_Kind_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('connect_kind');
		$this->setPagerVisibility(false);
		$this->setFilterVisibility(false);
		$this->setUseAjax(true);
	}

	/**
	 * two columns: checkbox and brand name
	 */

	protected function _prepareColumns()
	{
		$this->addColumn('connect_vendor_kind', array(
			'header_css_class' => 'a-center',
			'type' => 'checkbox',
			'name' => 'connect_vendor_kind',
			'values' => $this->_getSelectedKind(),
			'align' => 'center',
			'width' => '50px',
			'index' => 'regulation_kind_id',
			'filter' => false,
			'sortable' => false,
		));

		$this->addColumn('value', array(
			'header' => Mage::helper('ghregulation')->__('Document kind'),
			'align' => 'left',
			'index' => 'name',
			'filter' => false,
			'sortable'  => false,
		));

		parent::_prepareColumns();
	}

	protected function _prepareCollection()
	{
		/** @var Gh_Regulation_Model_Regulation_Kind $model */
		$model = Mage::getModel('ghregulation/regulation_kind');

		/** @var Gh_Regulation_Model_Resource_Regulation_Kind_Collection $collection */
		$collection = $model->getResourceCollection();

		$collection->getSelect()->order('name',Zend_Db_Select::SQL_ASC);

		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	/**
	 *
	 * @return array
	 */
	protected function _getSelectedKind()
	{	
	    /*
		$vendorId = $this->getVendorId();
		$collection = Mage::getModel('ghregulation/regulation_vendor_kind')->getCollection();
		$collection->getSelect()
			->columns(array('regulation_kind_id'))
			->where('main_table.vendor_id = ' . $vendorId);
        */ // do not use vendor_kind
        $collection = Mage::getResourceModel('ghregulation/regulation_kind');
		$kinds = array();
		foreach ($collection as $kind) {
			$kinds[] = $kind->getData('regulation_kind_id');
		}

		return $kinds;
	}

}