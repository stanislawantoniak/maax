<?php
class Zolago_Holidays_Block_Adminhtml_Holidays_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	
	public function __construct(){
    	parent::__construct();
       	$this->setId('holidaysList');
       	$this->setUseAjax(true);
       	$this->setDefaultSort('name');
       	$this->setFilterVisibility(false);
       	$this->setPagerVisibility(false);
	}
	
	protected function _prepareCollection(){
   		$collection = Mage::getModel('zolagoholidays/holiday')
                   		->getCollection();
   		$this->setCollection($collection);
		
	   	return parent::_prepareCollection();
	}
	
	protected function _prepareColumns(){
		
	   	$this->addColumn('holiday_id', array(
	       	'header'   => Mage::helper('zolagoholidays')->__('Id'),
	       	'width'    => 50,
	       	'index'    => 'holiday_id',
	       	'sortable' => false,
	   	));
       	$this->addColumn('name', array(
        	'header'   => Mage::helper('zolagoholidays')->__('Holiday name'),
           	'index'    => 'name',
           	'sortable' => false,
		));
       	$this->addColumn('country_id', array(
           	'header'   => Mage::helper('zolagoholidays')->__('Country'),
           	'index'    => 'country_id',
           	'sortable' => false,
       	));
		$this->addColumn('type', array(
           	'header'   => Mage::helper('zolagoholidays')->__('Type'),
           	'index'    => 'type',
           	'sortable' => false,
       	));
       	$this->addColumn('date', array(
       		'header'   => Mage::helper('zolagoholidays')->__('Date'),
           	'index'    => 'date',
           	'sortable' => false,
       	));
		$this->addColumn('exclude_from_delivery', array(
       		'header'   => Mage::helper('zolagoholidays')->__('Exclude from delivery'),
           	'index'    => 'exclude_from_delivery',
           	'sortable' => false,
       	));
		$this->addColumn('exclude_from_pickup', array(
       		'header'   => Mage::helper('zolagoholidays')->__('Exclude from pickup'),
           	'index'    => 'exclude_from_pickup',
           	'sortable' => false,
       	));
		
       	return parent::_prepareColumns();
   	}

	public function getRowUrl($row){
        return $this->getUrl('*/*/edit', array('holiday_id'=>$row->getId()));
    }
}
