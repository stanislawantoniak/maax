<?php
class Zolago_Holidays_Block_Adminhtml_Holidays_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	
	public function __construct(){
    	parent::__construct();
       	$this->setId('holidaysList');
       	$this->setUseAjax(true);
       	$this->setDefaultSort('name');
       	$this->setFilterVisibility(true);
       	$this->setPagerVisibility(true);
		
		// $country_id = Mage::helper('zolagoholidays')->getCurrentCountryId();
		// if($country_id){
			// $this->_defaultFilter = array('country_id'=>$country_id);
		// }
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
	       	'type'  => 'number',
	       	'sortable' => false,
	   	));
       	$this->addColumn('name', array(
        	'header'   => Mage::helper('zolagoholidays')->__('Holiday name'),
           	'index'    => 'name',
           	'sortable' => false,
           	'type'     => 'text'
		));
       	$this->addColumn('country_id', array(
           	'header'   => Mage::helper('zolagoholidays')->__('Country'),
           	'index'    => 'country_id',
           	'renderer' => 'Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Country',
           	'type'     => 'country',
           	'sortable' => false,
       	));
		$this->addColumn('type', array(
           	'header'   => Mage::helper('zolagoholidays')->__('Type'),
           	'index'    => 'type',
           	'renderer' => 'Zolago_Holidays_Block_Adminhtml_Holidays_Grid_Renderer_Type',
           	'type'     => 'options',
           	'options'  => Mage::getSingleton('zolagoholidays/holiday')->getTypes(),
           	'sortable' => false,
       	));
       	$this->addColumn('date', array(
       		'header'   => Mage::helper('zolagoholidays')->__('Date'),
           	'index'    => 'date',
           	'sortable' => false,
           	'type'     => 'text'
       	));
		$this->addColumn('exclude_from_delivery', array(
       		'header'   => Mage::helper('zolagoholidays')->__('Exclude from delivery'),
           	'index'    => 'exclude_from_delivery',
           	'renderer' => 'Zolago_Holidays_Block_Adminhtml_Holidays_Grid_Renderer_Boolean',
           	'sortable' => false,
           	'type'     => 'options',
           	'options'  => array(
				'1' => Mage::helper('zolagocommon')->__("Yes"),
				'0' => Mage::helper('zolagocommon')->__("No")
			)
       	));
		$this->addColumn('exclude_from_pickup', array(
       		'header'   => Mage::helper('zolagoholidays')->__('Exclude from pickup'),
           	'index'    => 'exclude_from_pickup',
           	'renderer' => 'Zolago_Holidays_Block_Adminhtml_Holidays_Grid_Renderer_Boolean',
           	'sortable' => false,
           	'type'     => 'options',
           	'options'  => array(
				'1' => Mage::helper('zolagocommon')->__("Yes"),
				'0' => Mage::helper('zolagocommon')->__("No")
			)
       	));
		
       	return parent::_prepareColumns();
   	}
	
	public function getGridUrl()
 	{
   		return $this->getUrl('*/*/grid', array('_current'=>true));
 	}
 
	public function getRowUrl($row){
        return $this->getUrl('*/*/edit', array('holiday_id'=>$row->getId()));
    }
}
