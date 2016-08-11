<?php
/**
 * MagPassion_Productcarousel extension
 * 
 * @category   	MagPassion
 * @package		MagPassion_Productcarousel
 * @copyright  	Copyright (c) 2014 by MagPassion (http://magpassion.com)
 * @license	http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * Product Carousel admin grid block
 *
 * @category	MagPassion
 * @package		MagPassion_Productcarousel
 * @author MagPassion.com
 */
class MagPassion_Productcarousel_Block_Adminhtml_Productcarousel_Grid extends Mage_Adminhtml_Block_Widget_Grid{
	/**
	 * constructor
	 * @access public
	 * @return void
	 * @author MagPassion.com
	 */
	public function __construct(){
		parent::__construct();
		$this->setId('productcarouselGrid');
		$this->setDefaultSort('entity_id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
		$this->setUseAjax(true);
	}
	/**
	 * prepare collection
	 * @access protected
	 * @return MagPassion_Productcarousel_Block_Adminhtml_Productcarousel_Grid
	 * @author MagPassion.com
	 */
	protected function _prepareCollection(){
		$collection = Mage::getModel('productcarousel/productcarousel')->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}
	/**
	 * prepare grid collection
	 * @access protected
	 * @return MagPassion_Productcarousel_Block_Adminhtml_Productcarousel_Grid
	 * @author MagPassion.com
	 */
	protected function _prepareColumns(){
		$this->addColumn('entity_id', array(
			'header'	=> Mage::helper('productcarousel')->__('Id'),
			'index'		=> 'entity_id',
			'type'		=> 'number'
		));
		$this->addColumn('blocktitle', array(
			'header'=> Mage::helper('productcarousel')->__('Block Title'),
			'index' => 'blocktitle',
			'type'	 	=> 'text',

		));
		$this->addColumn('type', array(
			'header'=> Mage::helper('productcarousel')->__('Type'),
			'index' => 'type',
			'type'		=> 'options',
			'options'	=> array(
				'new' => Mage::helper('productcarousel')->__('New products'),
				'mostview' => Mage::helper('productcarousel')->__('Most view products'),
				'custom' => Mage::helper('productcarousel')->__('Custom products'),
			)

		));
        $this->addColumn('category', array(
			'header'=> Mage::helper('productcarousel')->__('Category'),
			'index' => 'category',
			'type'	 	=> 'text',

		));
		$this->addColumn('status', array(
			'header'	=> Mage::helper('productcarousel')->__('Status'),
			'index'		=> 'status',
			'type'		=> 'options',
			'options'	=> array(
				'1' => Mage::helper('productcarousel')->__('Enabled'),
				'0' => Mage::helper('productcarousel')->__('Disabled'),
			)
		));
		if (!Mage::app()->isSingleStoreMode()) {
			$this->addColumn('store_id', array(
				'header'=> Mage::helper('productcarousel')->__('Store Views'),
				'index' => 'store_id',
				'type'  => 'store',
				'store_all' => true,
				'store_view'=> true,
				'sortable'  => false,
				'filter_condition_callback'=> array($this, '_filterStoreCondition'),
			));
		}
		$this->addColumn('created_at', array(
			'header'	=> Mage::helper('productcarousel')->__('Created at'),
			'index' 	=> 'created_at',
			'width' 	=> '120px',
			'type'  	=> 'datetime',
		));
		$this->addColumn('updated_at', array(
			'header'	=> Mage::helper('productcarousel')->__('Updated at'),
			'index' 	=> 'updated_at',
			'width' 	=> '120px',
			'type'  	=> 'datetime',
		));
		$this->addColumn('action',
			array(
				'header'=>  Mage::helper('productcarousel')->__('Action'),
				'width' => '100',
				'type'  => 'action',
				'getter'=> 'getId',
				'actions'   => array(
					array(
						'caption'   => Mage::helper('productcarousel')->__('Edit'),
						'url'   => array('base'=> '*/*/edit'),
						'field' => 'id'
					)
				),
				'filter'=> false,
				'is_system'	=> true,
				'sortable'  => false,
		));
		$this->addExportType('*/*/exportCsv', Mage::helper('productcarousel')->__('CSV'));
		$this->addExportType('*/*/exportExcel', Mage::helper('productcarousel')->__('Excel'));
		$this->addExportType('*/*/exportXml', Mage::helper('productcarousel')->__('XML'));
		return parent::_prepareColumns();
	}
	/**
	 * prepare mass action
	 * @access protected
	 * @return MagPassion_Productcarousel_Block_Adminhtml_Productcarousel_Grid
	 * @author MagPassion.com
	 */
	protected function _prepareMassaction(){
		$this->setMassactionIdField('entity_id');
		$this->getMassactionBlock()->setFormFieldName('productcarousel');
		$this->getMassactionBlock()->addItem('delete', array(
			'label'=> Mage::helper('productcarousel')->__('Delete'),
			'url'  => $this->getUrl('*/*/massDelete'),
			'confirm'  => Mage::helper('productcarousel')->__('Are you sure?')
		));
		$this->getMassactionBlock()->addItem('status', array(
			'label'=> Mage::helper('productcarousel')->__('Change status'),
			'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
			'additional' => array(
				'status' => array(
						'name' => 'status',
						'type' => 'select',
						'class' => 'required-entry',
						'label' => Mage::helper('productcarousel')->__('Status'),
						'values' => array(
								'1' => Mage::helper('productcarousel')->__('Enabled'),
								'0' => Mage::helper('productcarousel')->__('Disabled'),
						)
				)
			)
		));
		$this->getMassactionBlock()->addItem('type', array(
			'label'=> Mage::helper('productcarousel')->__('Change Type'),
			'url'  => $this->getUrl('*/*/massType', array('_current'=>true)),
			'additional' => array(
				'flag_type' => array(
						'name' => 'flag_type',
						'type' => 'select',
						'class' => 'required-entry',
						'label' => Mage::helper('productcarousel')->__('Type'),
						'values' => array(
								'1' => Mage::helper('productcarousel')->__('Yes'),
								'0' => Mage::helper('productcarousel')->__('No'),
						)
				)
			)
		));
		$this->getMassactionBlock()->addItem('skin', array(
			'label'=> Mage::helper('productcarousel')->__('Change Navigation Skin'),
			'url'  => $this->getUrl('*/*/massSkin', array('_current'=>true)),
			'additional' => array(
				'flag_skin' => array(
						'name' => 'flag_skin',
						'type' => 'select',
						'class' => 'required-entry',
						'label' => Mage::helper('productcarousel')->__('Navigation Skin'),
						'values' => array(
								'1' => Mage::helper('productcarousel')->__('Yes'),
								'0' => Mage::helper('productcarousel')->__('No'),
						)
				)
			)
		));
		$this->getMassactionBlock()->addItem('showblocktitle', array(
			'label'=> Mage::helper('productcarousel')->__('Change Show block title'),
			'url'  => $this->getUrl('*/*/massShowblocktitle', array('_current'=>true)),
			'additional' => array(
				'flag_showblocktitle' => array(
						'name' => 'flag_showblocktitle',
						'type' => 'select',
						'class' => 'required-entry',
						'label' => Mage::helper('productcarousel')->__('Show block title'),
						'values' => array(
								'1' => Mage::helper('productcarousel')->__('Yes'),
								'0' => Mage::helper('productcarousel')->__('No'),
						)
				)
			)
		));
		$this->getMassactionBlock()->addItem('showproductname', array(
			'label'=> Mage::helper('productcarousel')->__('Change Show product name'),
			'url'  => $this->getUrl('*/*/massShowproductname', array('_current'=>true)),
			'additional' => array(
				'flag_showproductname' => array(
						'name' => 'flag_showproductname',
						'type' => 'select',
						'class' => 'required-entry',
						'label' => Mage::helper('productcarousel')->__('Show product name'),
						'values' => array(
								'1' => Mage::helper('productcarousel')->__('Yes'),
								'0' => Mage::helper('productcarousel')->__('No'),
						)
				)
			)
		));
		$this->getMassactionBlock()->addItem('showproductimage', array(
			'label'=> Mage::helper('productcarousel')->__('Change Show product image'),
			'url'  => $this->getUrl('*/*/massShowproductimage', array('_current'=>true)),
			'additional' => array(
				'flag_showproductimage' => array(
						'name' => 'flag_showproductimage',
						'type' => 'select',
						'class' => 'required-entry',
						'label' => Mage::helper('productcarousel')->__('Show product image'),
						'values' => array(
								'1' => Mage::helper('productcarousel')->__('Yes'),
								'0' => Mage::helper('productcarousel')->__('No'),
						)
				)
			)
		));
		$this->getMassactionBlock()->addItem('showproductprice', array(
			'label'=> Mage::helper('productcarousel')->__('Change Show product price'),
			'url'  => $this->getUrl('*/*/massShowproductprice', array('_current'=>true)),
			'additional' => array(
				'flag_showproductprice' => array(
						'name' => 'flag_showproductprice',
						'type' => 'select',
						'class' => 'required-entry',
						'label' => Mage::helper('productcarousel')->__('Show product price'),
						'values' => array(
								'1' => Mage::helper('productcarousel')->__('Yes'),
								'0' => Mage::helper('productcarousel')->__('No'),
						)
				)
			)
		));
		$this->getMassactionBlock()->addItem('showproductaddtocart', array(
			'label'=> Mage::helper('productcarousel')->__('Change Show add to cart button'),
			'url'  => $this->getUrl('*/*/massShowproductaddtocart', array('_current'=>true)),
			'additional' => array(
				'flag_showproductaddtocart' => array(
						'name' => 'flag_showproductaddtocart',
						'type' => 'select',
						'class' => 'required-entry',
						'label' => Mage::helper('productcarousel')->__('Show add to cart button'),
						'values' => array(
								'1' => Mage::helper('productcarousel')->__('Yes'),
								'0' => Mage::helper('productcarousel')->__('No'),
						)
				)
			)
		));
		$this->getMassactionBlock()->addItem('showproductmore', array(
			'label'=> Mage::helper('productcarousel')->__('Change Show more content of product'),
			'url'  => $this->getUrl('*/*/massShowproductmore', array('_current'=>true)),
			'additional' => array(
				'flag_showproductmore' => array(
						'name' => 'flag_showproductmore',
						'type' => 'select',
						'class' => 'required-entry',
						'label' => Mage::helper('productcarousel')->__('Show more content of product'),
						'values' => array(
								'1' => Mage::helper('productcarousel')->__('Yes'),
								'0' => Mage::helper('productcarousel')->__('No'),
						)
				)
			)
		));
		$this->getMassactionBlock()->addItem('showmoreprice', array(
			'label'=> Mage::helper('productcarousel')->__('Change Show more: product price'),
			'url'  => $this->getUrl('*/*/massShowmoreprice', array('_current'=>true)),
			'additional' => array(
				'flag_showmoreprice' => array(
						'name' => 'flag_showmoreprice',
						'type' => 'select',
						'class' => 'required-entry',
						'label' => Mage::helper('productcarousel')->__('Show more: product price'),
						'values' => array(
								'1' => Mage::helper('productcarousel')->__('Yes'),
								'0' => Mage::helper('productcarousel')->__('No'),
						)
				)
			)
		));
		$this->getMassactionBlock()->addItem('showmorereview', array(
			'label'=> Mage::helper('productcarousel')->__('Change Show more: product review'),
			'url'  => $this->getUrl('*/*/massShowmorereview', array('_current'=>true)),
			'additional' => array(
				'flag_showmorereview' => array(
						'name' => 'flag_showmorereview',
						'type' => 'select',
						'class' => 'required-entry',
						'label' => Mage::helper('productcarousel')->__('Show more: product review'),
						'values' => array(
								'1' => Mage::helper('productcarousel')->__('Yes'),
								'0' => Mage::helper('productcarousel')->__('No'),
						)
				)
			)
		));
		$this->getMassactionBlock()->addItem('showmoredes', array(
			'label'=> Mage::helper('productcarousel')->__('Change Show more: product short description'),
			'url'  => $this->getUrl('*/*/massShowmoredes', array('_current'=>true)),
			'additional' => array(
				'flag_showmoredes' => array(
						'name' => 'flag_showmoredes',
						'type' => 'select',
						'class' => 'required-entry',
						'label' => Mage::helper('productcarousel')->__('Show more: product short description'),
						'values' => array(
								'1' => Mage::helper('productcarousel')->__('Yes'),
								'0' => Mage::helper('productcarousel')->__('No'),
						)
				)
			)
		));
		$this->getMassactionBlock()->addItem('showmoreaddtocart', array(
			'label'=> Mage::helper('productcarousel')->__('Change Show more: product add to cart'),
			'url'  => $this->getUrl('*/*/massShowmoreaddtocart', array('_current'=>true)),
			'additional' => array(
				'flag_showmoreaddtocart' => array(
						'name' => 'flag_showmoreaddtocart',
						'type' => 'select',
						'class' => 'required-entry',
						'label' => Mage::helper('productcarousel')->__('Show more: product add to cart'),
						'values' => array(
								'1' => Mage::helper('productcarousel')->__('Yes'),
								'0' => Mage::helper('productcarousel')->__('No'),
						)
				)
			)
		));
		$this->getMassactionBlock()->addItem('showmoreaddtolink', array(
			'label'=> Mage::helper('productcarousel')->__('Change Show more: prouduct add to link'),
			'url'  => $this->getUrl('*/*/massShowmoreaddtolink', array('_current'=>true)),
			'additional' => array(
				'flag_showmoreaddtolink' => array(
						'name' => 'flag_showmoreaddtolink',
						'type' => 'select',
						'class' => 'required-entry',
						'label' => Mage::helper('productcarousel')->__('Show more: prouduct add to link'),
						'values' => array(
								'1' => Mage::helper('productcarousel')->__('Yes'),
								'0' => Mage::helper('productcarousel')->__('No'),
						)
				)
			)
		));
		return $this;
	}
	/**
	 * get the row url
	 * @access public
	 * @param MagPassion_Productcarousel_Model_Productcarousel
	 * @return string
	 * @author MagPassion.com
	 */
	public function getRowUrl($row){
		return $this->getUrl('*/*/edit', array('id' => $row->getId()));
	}
	/**
	 * get the grid url
	 * @access public
	 * @return string
	 * @author MagPassion.com
	 */
	public function getGridUrl(){
		return $this->getUrl('*/*/grid', array('_current'=>true));
	}
	/**
	 * after collection load
	 * @access protected
	 * @return MagPassion_Productcarousel_Block_Adminhtml_Productcarousel_Grid
	 * @author MagPassion.com
	 */
	protected function _afterLoadCollection(){
		$this->getCollection()->walk('afterLoad');
		parent::_afterLoadCollection();
	}
	/**
	 * filter store column
	 * @access protected
	 * @param MagPassion_Productcarousel_Model_Resource_Productcarousel_Collection $collection
	 * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
	 * @return MagPassion_Productcarousel_Block_Adminhtml_Productcarousel_Grid
	 * @author MagPassion.com
	 */
	protected function _filterStoreCondition($collection, $column){
		if (!$value = $column->getFilter()->getValue()) {
        	return;
		}
		$collection->addStoreFilter($value);
		return $this;
    }
}